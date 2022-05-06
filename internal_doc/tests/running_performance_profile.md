# Running a performance profile in Akeneo PIM

We use Blackfire as profiling tool.

## Credentials

You can get the credentials from Akeneo Blackfire Account.
There are 4 credentials to get:
- the client id
- the client token
- the server id
- the server token

## Profiling CLI

We will profile a phpunit test in this example:

```
export BLACKFIRE_CLIENT_ID=client_id
export BLACKFIRE_CLIENT_TOKEN=client_token
export BLACKFIRE_SERVER_ID=server_id
export BLACKFIRE_SERVER_TOKEN=server_token

docker-compose run --rm php blackfire run vendor/bin/phpunit -c phpunit.xml.dist tests/back/Pim/Enrichment/EndToEnd/Category/ExternalApi/CreateCategoryEndToEnd.php --filter testHttpHeadersInResponseWhenACategoryIsCreated
```

## Profiling HTTP request

We will profile the HTTP request to get the login page in this example:

```
export BLACKFIRE_CLIENT_ID=client_id
export BLACKFIRE_CLIENT_TOKEN=client_token
export BLACKFIRE_SERVER_ID=server_id
export BLACKFIRE_SERVER_TOKEN=server_token

docker-compose run --rm php blackfire curl 'http://httpd/user/login'
```

Do note that we replaced `localhost` by `httpd` as we are inside the docker.
