# Pub/Sub

## Topics

- `product`

## Development

With env var `PUBSUB_EMULATOR_HOST=` defined `projectId: emulator-project`

### Init

```sh
docker-compose exec fpm curl -X PUT http://pubsub-emulator:8085/v1/projects/emulator-project/topics/product
```

## Message Consumption

```sh
docker-compose exec -u www-data fpm bin/console enqueue:consume -vvv
```
