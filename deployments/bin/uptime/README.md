# Clear uptime check

# Description

This script allows you to automatically interact with google cloud api (see "operations" section)

By default, this script runs on the akecld-saas-dev project but the target project can be modified via the use of environment variables (cf environment)

## Operations

### Deployment:uptime:clear

First we search all metrics starting with pim(ci|up) among all google cloud metrics and we aggregate them by summing their test results over all locations. (gcloud metric api)

_Exemple :_

_Raw data :_
|metric|location|9h|9h10|9h20|
|--|--|--|--|--|
|pimci-123|usa-1|1|1|1|
|pimci-123|europe-1|1|1|1|
|pimci-123|asia-1|1|1|1|
|pimci-456|usa-1|0|0|0|
|pimci-456|europe-1|0|0|0|
|pimci-456|asia-1|0|0|0|
|pimup-789|usa-1|0|0|0|
|pimup-789|europe-1|1|1|1|
|pimup-789|asia-1|1|0|0|

_Aggregation :_

|metric|location|9h|9h10|9h20|
|--|--|--|--|--|
|pimci-123|-|3|3|3|
|pimci-456|-|0|0|0|
|pimup-789|-|2|1|1|

This aggregation shows that pimci-456 doesn't have any valid test with the last 9 checks over any location so we can assume that this uptimecheck can be removed.

To be sure, we will check if this uptime still exist, and is not an empty data set from an old uptime, so we will get its configuration (gcloud monitoring api).
If there is none, we can't delete it

if there is a configuration, we remove the corresponding uptime (gcloud monitoring api)

### deployment:uptime:get

List uptime check success rate over time by check_id only for period from wednesday 18h CEST to 21h CEST

_Exemple :_

_Raw data :_
|metric|location|9h|9h10|9h20|
|--|--|--|--|--|
|pimci-123|usa-1|1|1|1|
|pimci-123|europe-1|1|1|1|
|pimci-123|asia-1|1|1|1|
|pimci-456|usa-1|0|0|0|
|pimci-456|europe-1|0|0|0|
|pimci-456|asia-1|0|0|0|
|pimup-789|usa-1|0|0|0|
|pimup-789|europe-1|1|1|1|
|pimup-789|asia-1|1|0|0|

_Aggregation :_

|metric|location|9h|9h10|9h20|
|--|--|--|--|--|
|pimci-123|-|3/3|3/3|3/3|
|pimci-456|-|0/3|0/3|0/3|
|pimup-789|-|2/3|1/3|1/3|


# How to use

## Image build
Before building this image, you must authenticate to gcloud
```bash
gcloud auth login
```
and then you can build and push
```bash
make buildPush
```

## Gcloud authentication
First generate your gcloud authentication json file
```bash
gcloud auth application-default login
```

## Execute
### Local

The php extension bcmath is mandatory and you'll have to install it first

```bash
composer install
GOOGLE_APPLICATION_CREDENTIALS=~/.config/gcloud/application_default_credentials.json ./deployment deployment-uptime-<get|clear>
```

### Docker
```bash
make install
GOOGLE_APPLICATION_CREDENTIALS=~/.config/gcloud/application_default_credentials.json make deployment-uptime-<get|clear>
```

# Environment

* **PROJECT** : gcloud project (default : akecld-saas-dev)
* **LOG_LEVEL** : set log level (default : WARNING) (cf [monolog loglevel](https://github.com/Seldaek/monolog/blob/main/doc/01-usage.md#log-levels))
* **GOOGLE_APPLICATION_CREDENTIALS** : Path to gcloud authentication json file

# Sample

```bash
LOG_LEVEL=info PROJECT=akecld-saas-dev php ./deployment deployment:uptime:get
```
