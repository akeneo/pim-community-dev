Catalogs - setup
================

## Requirements

Your PIM is running normally using docker.
```shell
make dependencies pim-dev
```

## Setup

Open your terminal in the root directory of the PIM and follow these steps:

**#1: Fixtures**
```shell
make catalogs-fixtures
```

**#2: Start the catalogs UI**

We use an isolated *create-react-app* environment to develop the catalogs UI:
```shell
yarn workspace @akeneo-pim-community/catalogs start
```

It will automatically open your browser to `localhost:3000` with live reload enabled.

> **Warning**
> Running this *create-react-app* with docker-compose is currently not supported, you need *yarn* on the host.
