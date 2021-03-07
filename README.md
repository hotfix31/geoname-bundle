Geonames Bundle
===============
# Introduction

Provides access to the data exported by [geonames.org][1] into [Symfony 4][2] and [Symfony 5][2]
applications. Inspired by [bordeux/geoname-bundle](https://github.com/bordeux/geoname-bundle)

## What is [geonames.org][1]

From the geonames.org website:

> The GeoNames geographical database covers all countries and contains over
> eight million placenames that are available for download free of charge.

## When to use this bundle

Most useful application for this bundle is to normalize the geograhical
information stored in your database such as Countries, States and Cities. Thanks
to the extensive [geonames.org][1] data almost all cities, towns and suburbs are
covered worldwide.

## Features

- Imports the following geonames.org data:

    * Countries
    * Timezones
    * States & Provences
    * Cities, Towns, Suburbs, Villages etc.
    * Alternate names
    * Hierarchy

- Provides the following data store implementations:

    * Doctrine ORM

# Installation

1. Install the bundle using composer:

```bash
composer require hotfix/geoname-bundle
```

2. Add the bundle to your `AppKernel.php`

```php
// AppKernel::registerBundles()
$bundles = array(
    // ...
        new Hotfix\Bundle\GeoNameBundle\HotfixGeoNameBundle(),
    // ...
);
```

## Install or update database schema

Execute the migrations using the supplied migration configuration

```bash
    php bin/console doctrine:schema:update --force
```

## Import the data

**Note** that importing the data from the remote geonames.org repository involves downloading
almost 350 MB data from [geonames.org][1].

The following commands can be used in sequence to load all supported data from
the [geonames.org][1] export (http://download.geonames.org/export/dump)

### Import data

Loads a list of all data from [geonames.org][1]

```bash
    php bin/console hotfix:geoname:import  --env=prod
```

### Options

```
Usage:
  hotfix:geoname:import [options]

Options:
  -o, --download-dir[=DOWNLOAD-DIR]      Download dir
  -f, --feature-codes=FEATURE-CODES      Feature-codes files [default: "https://download.geonames.org/export/dump/featureCodes_en.txt"]
      --skip-feature-codes               options to skip import feature-codes
  -t, --timezones=TIMEZONES              Timezones files [default: "https://download.geonames.org/export/dump/timeZones.txt"]
      --skip-timezones                   options to skip import timezones
  -a1, --admin1=ADMIN1                   Admin1 files [default: "https://download.geonames.org/export/dump/admin1CodesASCII.txt"]
      --skip-admin1                      options to skip import admin1
  -a2, --admin2=ADMIN2                   Admin2 files [default: "https://download.geonames.org/export/dump/admin2Codes.txt"]
      --skip-admin2                      options to skip import admin2
  -g, --geonames=GEONAMES                Geonames files [default: "https://download.geonames.org/export/dump/allCountries.zip"]
      --skip-geonames                    options to skip import geonames
  -c, --countries=COUNTRIES              Countries files [default: "https://download.geonames.org/export/dump/countryInfo.txt"]
      --skip-countries                   options to skip import countries
  -i, --hierarchy=HIERARCHY              Hierarchy files [default: "https://download.geonames.org/export/dump/hierarchy.zip"]
      --skip-hierarchy                   options to skip import hierarchy
  -l, --alternate-names=ALTERNATE-NAMES  Alternate-names files [default: "https://download.geonames.org/export/dump/alternateNamesV2.zip"]
      --skip-alternate-names             options to skip import alternate-names
```

 [1]: http://geonames.org
 [2]: http://symfony.com