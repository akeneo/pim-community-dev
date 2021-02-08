# Akeneo Messenger Bundle

This bundle provides the missing pieces to integrate [Symfony Messenger](https://symfony.com/doc/4.4/messenger.html) with the PIM.

## Messenger Transport for Google Pub/Sub

The Transport requires the library ["google/cloud-pubsub"](https://packagist.org/packages/google/cloud-pubsub).

It follows the official Symfony documentation on [creating a custom Transport](https://symfony.com/doc/4.4/messenger/custom-transport.html).

The environment variable `SRNT_GOOGLE_APPLICATION_CREDENTIALS` must be defined with the file path of the JSON file that contains your [service account key](https://cloud.google.com/docs/authentication/getting-started#setting_the_environment_variable).

### Simple queue configuration

For a simple configuration of a Pub/Sub topic with only one subscription:

```yml
framework:
  messenger:
    transports:
      my_queue:
        dsn: 'gps:'
        options:
          project_id: '%env(GOOGLE_CLOUD_PROJECT)%'
          topic_name: '%env(PUBSUB_TOPIC)%'
          subscription_name: '%env(PUBSUB_SUBSCRIPTION)%'

    routing:
      'My\Event': my_queue
```

### Queue with multiple subscribers

Google Pub/Sub uses a [subscription model](https://en.wikipedia.org/wiki/Publish%E2%80%93subscribe_pattern) and it means that one topic can have more than one subscription.

To be able to handle this, we recommend having multiple transport definitions with one that serves as Producer only while the other ones are Consumers.

```yml
framework:
  messenger:
    transports:
      # Producer

      my_producer:
        dsn: 'gps:'
        options:
          project_id: '%env(GOOGLE_CLOUD_PROJECT)%'
          topic_name: '%env(PUBSUB_TOPIC)%'

      # Consumers

      my_first_consumer:
        dsn: 'gps:'
        options:
          project_id: '%env(GOOGLE_CLOUD_PROJECT)%'
          topic_name: '%env(PUBSUB_TOPIC)%'
          subscription_name: '%env(PUBSUB_SUBSCRIPTION_1)%'

      my_second_consumer:
        dsn: 'gps:'
        options:
          project_id: '%env(GOOGLE_CLOUD_PROJECT)%'
          topic_name: '%env(PUBSUB_TOPIC)%'
          subscription_name: '%env(PUBSUB_SUBSCRIPTION_2)%'

    routing:
      'My\Event': my_producer
```

From the Symfony Messenger point of view, these are three independent queues. But from Pub/Sub point of view, all messages sent to the producer will be dispatched to the consumers.

### Transport Options

- `topic_name: string`

- `subscription_name: ?string`

  Optional, but if the option is not defined you won't be able to receive messages from this Transport.

- `auto_setup: ?bool`

  Default to `false`, but can be enabled to make the Transport create the topic and subscription for you.
  This is useful when using the in-memory [Pub/Sub emulator](https://cloud.google.com/pubsub/docs/emulator) (enabled when the environment variable `PUBSUB_EMULATOR_HOST` is defined).

## Purge Command for the Doctrine Transport table

Define a Command to purge the table configured on the Doctrine Transport.

```sh
bin/console akeneo:messenger:doctrine:purge-messages <table-name> <queue-name>
```

The goal is to remove old messages (the default retention time is 2 hours).
