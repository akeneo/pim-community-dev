Oro Sync Bundle
===============
Provides websocket comminucation layer. Based on JDareClankBundle.

## Installation ##
Add the `oro/sync-bundle` package to your `require` section in the `composer.json` file.

``` yaml
"require": {
    [...]
    "oro/sync-bundle": "dev-master"
},
"repositories": [
    {
        "type": "vcs",
        "url": "git@github.com:laboro/SyncBundle.git",
        "branch": "master"
    }
]
```

Add the SyncBundle to your application's kernel:

``` php
<?php
public function registerBundles()
{
    $bundles = array(
        // ...
        new Oro\Bundle\SyncBundle\OroSyncBundle(),
        // ...
    );
    ...
}
```

## Configuration ##
Set port and host (optional) for websocket server in parameters.yml
``` yaml
    websocket_host: '*'
    websocket_port: 8080
```

Add the following to your app/config.yml
``` yaml
clank:
    web_socket_server:
        port:                 %websocket_port%               # The port the socket server will listen on
        host:                 %websocket_host%               # (optional) The host ip to bind to
    session_handler:          session.handler.pdo            # Any session handler except native (files)
    periodic:
        -
            service:          "oro_wamp.db_ping"
            time:             60000                          # the time in milliseconds between the "tick" function being called

framework:
    session:
        handler_id:           session.handler.pdo

# session handler config (PDO)
services:
    doctrine.dbal.default.wrapped_connection:
        factory_service:      doctrine.dbal.default_connection
        factory_method:       getWrappedConnection
        class:                PDO
    session.handler.pdo:
        class:                Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler
        arguments:
            - "@doctrine.dbal.default.wrapped_connection"
            -
              db_table:       oro_session
              db_id_col:      id
```

## Usage ##
You should be able to run this from the root of your symfony installation:

``` bash
php app/console clank:server
```

If everything is successful, you will see something similar to the following:

``` bash
Starting Clank
Launching Ratchet WS Server on: *:8080
```

This means the websocket server is now up and running!

Other documentation could be found [here](https://github.com/JDare/ClankBundle#resources).