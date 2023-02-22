# How to add a queue

- If you want to add a queue: create your message object
Each queue has its own message object.

Example:

```php
use Akeneo\Pim\Platform\Messaging\Domain\MessageTenantAwareInterface;
use Akeneo\Pim\Platform\Messaging\Domain\SerializableMessageInterface;
use Akeneo\Pim\Platform\Messaging\Domain\TenantAwareTrait;

final class YourMessage implements MessageTenantAwareInterface, SerializableMessageInterface
{
    use TenantAwareTrait;

    public function __construct(public readonly string $text)
    {
    }

    public function normalize(): array
    {
        return ['text' => $this->text];
    }

    public static function denormalize(array $normalized): SerializableMessageInterface
    {
        Assert::keyExists($normalized, 'text');
        Assert::string($normalized['text']);

        return new YourMessage($normalized['text']);
    }
}
```

Normalization and denormalization are needed to store the message in the queue system. 
By implementing `SerializableMessageInterface` the message will be automatically normalized/denormalized using the
according methods. If you need more complex (de)normalization, you can create your own normalizer service
and tag it with `akeneo_batch_queue.messenger.normalizer`.

Now you certainly need to add a consumer, this is the next section.


- If you want to add a consumer: create your handler

```php
final class YourMessageHandler
{
    public function __invoke(YourMessage $message)
    {
        // Your logic
    }
}
```

And define the service with the tag:

```yaml
services:
    your_service_handler: ~
```


- Edit the `config/messaging.yml` file.
You can add many queues and many consumers by queue

```yaml
queues:

    your_queue:
        messageClass: Akeneo\..\YourMessage
        consumers:
            - name: dqi_launch_product_and_product_model_evaluations_consumer
              service_handler: 'your_service_handler'

```

- Generate the deployment config

TODO
