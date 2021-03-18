# Akeneo Messenger Bundle

This bundle provides the missing pieces to integrate [Symfony Messenger](https://symfony.com/doc/4.4/messenger.html) with the PIM.

## Messenger transport for Google Pub/Sub

The transport requires the library ["google/cloud-pubsub"](https://packagist.org/packages/google/cloud-pubsub).

It follows the official Symfony documentation on [creating a custom transport](https://symfony.com/doc/4.4/messenger/custom-transport.html).

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

  Optional, but if the option is not defined you won't be able to receive messages from this transport.

- `auto_setup: ?bool`

  Default to `false`, but can be enabled to make the transport create the topic and subscription for you.
  This is useful when using the in-memory [Pub/Sub emulator](https://cloud.google.com/pubsub/docs/emulator) (enabled when the environment variable `PUBSUB_EMULATOR_HOST` is defined).

- `ack_message_right_after_pull: ?bool`

  Default to `false`, it allows to ack the message right after pulling it.  
  By default Google Pub/Sub waits for an acknowledgement during the next 10 seconds after the message is pulled. After that the message is available once again for another subscriber using the same subscription.  
  It can be a problem because in Symfony Messenger the message is acknowledged after the message is handled. For long processes the message will not be acknowledged in time.  
  The maximum custom deadline you can specify is 600 seconds (10 minutes). If this limit is not high enough, consider set this option to `true`.  
  (See the `ackDeadlineSeconds` option in https://cloud.google.com/pubsub/docs/reference/rest/v1/projects.subscriptions/create)

- `filter: ?string`

  Default to `null`, it allows to filter messages. See https://cloud.google.com/pubsub/docs/filtering for the syntax.  
  Be careful with this feature, currently the filter cannot be updated. It is defined only at the subscription creation.  

## Purge command for the Doctrine transport table

Define a command to purge the Doctrine transport database table of outdated messages.

```sh
bin/console akeneo:messenger:doctrine:purge-messages <table-name> <queue-name>
```

The retention time can be specified with the option `--retention-time=<seconds>`, which has a default value of 7200 seconds (or 2 hour).
