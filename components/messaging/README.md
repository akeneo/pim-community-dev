# Messaging

## Presentation

The goal of this component is to facilitate the set up of queues and consumers.

## How it works

- Define your queues and/or your consumers in a simple config file
- If you add a queue: create your object message
- If you add a consumer: create your handler
- Generate the new terraform code to deploy them (TODO)

The component uses Symfony Messenger to configure the queue according to the environment

| Env       | Transport         |
---------------------------------
| dev       | doctrine          |
| test      | PubSub            |
| test_fake | In Memory         |
| behat     | PubSub            |
| prod      | doctrine / PubSub |

## Documentation

- [How to add a queue?](docs/how-to-add-a-queue.md)
