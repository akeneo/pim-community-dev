Catalogs
========

## Features

Checkout the directory [features](https://github.com/akeneo/pim-community-dev/blob/master/components/catalogs/features)
to discover the list of features supported by Catalogs.

## Tests

To run all tests of Catalogs, make sure that your PIM is running normally, then,
execute the following commands:
```shell
APP_ENV=test make database
make catalogs-tests
```

## UI Development

The UI is developed as close as possible of the **micro-frontend** pattern.  
A few components are exposed publicly and can be used as libraries by the PIM monolith.

If you want to work on these components, you don't have to open the PIM,
we provide an isolated `create-react-app` application.

Make sure that your PIM is running normally, then,
in the root directory of the PIM, execute the following commands:
```shell
make catalogs-fixtures
yarn workspace @akeneo-pim-community/catalogs start

# or with docker compose 
docker-compose run -p 3000:3000 --rm node yarn workspace @akeneo-pim-community/catalogs start
```
It will automatically open your browser.
