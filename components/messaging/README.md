# Messaging

## Presentation

The goal of this component is to facilitate the set up of queues and consumers.

The component uses Symfony Messenger to configure the queue according to the environment

| Env        | Transport         |
|------------|-------------------|
| dev        | doctrine          |
| test       | PubSub            |
| test_fake  | In Memory         |
| behat      | PubSub            |
| prod       | doctrine / PubSub |

## How it works

```mermaid
flowchart LR
    subgraph Tenant aware process
        action[Something happens in the PIM]
    end
    action -- Publish message in topic --> queue[(Multi-tenant queue)]
    Consumer1 <-- Ask messages for subscription1? --> queue
    subgraph Tenant agnostic daemon
        Consumer1 -- Launch command in a subprocess with tenant --> pmc1[processMessageCommand]
        subgraph Tenant aware process
            pmc1[processMessageCommand] -- Launch the final handler --> Handler1
        end
    end
    Consumer2 <-- Ask messages for subscription2? --> queue
    subgraph Tenant agnostic daemon
        Consumer2 -- Launch command in a subprocess with tenant --> pmc2[processMessageCommand]
        subgraph Tenant aware process
            pmc2[processMessageCommand] -- Launch the final handler --> Handler2
        end
    end
```

## Documentation

- [How to add a queue?](docs/how-to-add-a-queue.md)
