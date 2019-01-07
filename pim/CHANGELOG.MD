
# New version

## 2.3.23-02
- Deploy/infra changes only: clean and homogenize with helm-onboarer (mailgun_login)

# Precedent versions

## 2.3.23-01
- Update akeneo/pim-enterprise-dev to 2.3.23

## 2.3.22-00
### Features
- Require "akeneo/pim-enterprise-dev": "2.3.22"
- [CLOUD-767] Fix env conf when onboarder enabled for the fpm-init command 

## 2.3.21-01
### Features
- [CLOUD-767] Add pim-onboarder bundle.
- add dump-require-paths && yarn run webpack to the fpm-init command 
- hook-delete-policy for upgrade hooks changed to "before-hook-creation"

## 2.0.0
## Features
- Chart can be deployed on the clusters starting from cloud-saas v3.0.0
- Logging and Monitoring is now handled by Stackdriver
- Error Reporting is enabled for fpm

## 0.1.0
## Features
- First release of the chart
