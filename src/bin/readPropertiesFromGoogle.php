<?php
// include your composer dependencies
require_once 'vendor/autoload.php';

$nugget = new \Activerules\Nugget\Nugget();

$shortopts = "";
$shortopts .= "s:";  // Required - Google Spreadsheet ID
$shortopts .= "p:";  // Required - path to property definitions directory
$shortopts .= "c:";  // Required - path to credentials file for Google Drive API

$options = getopt($shortopts);

// Google Spreadsheet ID
$spreadsheetID = $options['s'];

// Properties are stored here.
// They can be extended and reused across objects.
define('PROPERTYDIR', realpath($options['p']));


define('SCOPES', implode(' ', array(Google_Service_Sheets::SPREADSHEETS_READONLY)));

putenv('GOOGLE_APPLICATION_CREDENTIALS=' . realpath($options['c']));

$client = new Google_Client();
$client->setScopes(SCOPES);
$client->useApplicationDefaultCredentials();

$service = new Google_Service_Sheets($client);

$range = 'IncomingProperties!A1:C';
$response = $service->spreadsheets_values->get($spreadsheetID, $range);
$properties = $response->getValues();

// Process the fetched properties
processProperties($properties);

/**
 *
 * @param type $properties
 */
function processProperties($properties)
{
    foreach ($properties as $propertyRow) {
        processPropertyRow($propertyRow);
    }
}

/**
 *
 * @param array $propertyRow
 */
function processPropertyRow($propertyRow)
{
    if (count($propertyRow) === 3) {
        $property = [];
        $property['name'] = $propertyRow[0];
        $property['types'] = explode(' or ', $propertyRow[1]);
        $property['description'] = $propertyRow[2];

        processProperty($property);
    }
}

/**
 *
 * @param string $property
 * @return string
 */
function processProperty($property)
{
    // If the property file already exists we don't do anything.
    if (propertyExists($property)) {
        return;
    }

    createProperty($property);
}

/**
 *
 * @param string $property
 */
function createProperty($property)
{
    // Save property name
    $propertyName = $property['name'];
    unset($property['name']);

    hydrateTypes($property);

    writeProperty($property, $propertyName);
}

/**
 *
 * @param string $property
 */
function hydrateTypes(& $property)
{
    $types = $property['types'];
    unset($property['types']);

    if (count($types) === 1) {
        hydrateSingleType($property, $types[0]);
    } else {
        hydrateOneOfType($property, $types);
    }
}

/**
 *
 * @param string $property
 * @param array $types
 */
function hydrateOneOfType(& $property, $types)
{
    $resolvedTypes = [];

    foreach ($types as $type) {
        $typeData = resolveType($type);
        $resolvedTypes[] = $typeData;
    }

    $resolved['oneOf'] = $resolvedTypes;

    $property = array_merge($property, $resolved);
}

/**
 *
 * @param string $property
 * @param string $type
 */
function hydrateSingleType(& $property, $type)
{
    $typeData = resolveType($type);

    $property = array_merge($property, $typeData);
}

/**
 *
 * @param string $type
 * @return string
 */
function resolveType($type)
{
    $nugget = new \Activerules\Nugget\Nugget();

    $jsonType = $nugget->jsonType($type);

    $resolved = []; // Array of resolved types

    if ($jsonType) {
        $resolved['nuggetType'] = $type;
        $resolved['type'] = $jsonType;
    } else {
        // Assume we got a valid Schema name
        $resolved['$ref'] = "#/components/schema/" . $type;
    }

    return $resolved;
}

/**
 *
 * @param string $property
 * @param string $propertyName
 */
function writeProperty($property, $propertyName)
{
    // JSON encode the obj to get a valid spec JSON
    $spec = json_encode($property, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    // Create a writeable file pointer to the OpenAPI schema location
    $file = fopen(PROPERTYDIR . '/' . $propertyName . '.json', 'w');

    // Write the spec to the file pointer
    fwrite($file, $spec);

    // Close the file pointer
    fclose($file);
}

/**
 *
 * @param type $property
 * @return type
 */
function propertyExists($property)
{
    return file_exists(PROPERTYDIR + DIRECTORY_SEPARATOR + $property['name'] + '.json');
}
