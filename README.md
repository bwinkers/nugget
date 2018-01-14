# nugget
[![Coverage Status](https://coveralls.io/repos/github/bwinkers/nugget/badge.svg?branch=master)](https://coveralls.io/github/bwinkers/nugget?branch=master)
[![Maintainability](https://api.codeclimate.com/v1/badges/aa0a4d97bb3af8d0a7a6/maintainability)](https://codeclimate.com/github/bwinkers/nugget/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/aa0a4d97bb3af8d0a7a6/test_coverage)](https://codeclimate.com/github/bwinkers/nugget/test_coverage)

A PHP7 for defining, validating and processing JSON business objects.

The ActiveRules Nugget Schema treats all data as `nuggets`.
These `nuggets` can be defined, and related using JSON files.
Rules can then be applied to any changes to an object.

## Testing 

## Local only tests (run by Travis CI)

```php vendor/bin/phpunit```

## Using remote schema (broken on Travis CI)

```php vendor/bin/phpunit tests/Activerules/Nugget/NuggetTest-Dev.php --testdox```

## Generate Property definitions from CSV files

Import property name, type and description from a CSV file.
More options will be added later.

```
php src/bin/convertCSVToProperties.php
```

## Generate Open API schema objects

ActiveRules object definitions are converted to Open API schema objects composed of property definitions.

```
php src/bin/o2s.php -p "./properties" -s "./schema" -o "./objects"
```

```
php src/bin/readPropertiesFromGoogle.php -s "1NeU79bJ-Zic-fwKK2PPuxntNXnbkyMKf6ZTIUHp1n4s" -c ~/.google/izzup-client_secret.json -p properties
```

## References
JSON References ($ref): [https://tools.ietf.org/html/draft-pbryan-zyp-json-ref-03](https://tools.ietf.org/html/draft-pbryan-zyp-json-ref-03)
JSON Schema: [http://json-schema.org/](http://json-schema.org/)
Schemas: [http://schema.org/](http://schema.org/)