# How to add a queue

- If you want to add a queue: create your message object
Each queue has its own message object.

Example:

```php
use Akeneo\Tool\Component\Messenger\TraceableMessageInterface;
use Akeneo\Tool\Component\Messenger\NormalizableMessageInterface;
use Akeneo\Tool\Component\Messenger\TraceableMessageTrait;

final class YourMessage implements TraceableMessageInterface, NormalizableMessageInterface
{
    use TraceableMessageTrait;

    public function __construct(public readonly string $text)
    {
    }

    public function normalize(): array
    {
        return ['text' => $this->text];
    }

    public static function denormalize(array $normalized): YourMessage
    {
        Assert::keyExists($normalized, 'text');
        Assert::string($normalized['text']);

        return new YourMessage($normalized['text']);
    }
}
```

Your message *need* to implement `TraceableMessageInterface` in order to be taken in charge by the bundle.

But implementing the `NormalizableMessageInterface` is optional. Normalization and denormalization are needed to store the message in the queue system. 
By implementing `NormalizableMessageInterface` the message will be automatically normalized/denormalized using the according methods.
If you need more complex (de)normalization (for instance injecting some extra values), don't implement `NormalizableMessageInterface`, 
you can create your own normalizer service and tag it with `akeneo_messenger.message.normalizer`.

Now you certainly need to add a handler, this is the next section.


- If you want to add a consumer: create your handler

It's as simple as create a service with an `__invoke()` method.

```php
use Akeneo\Tool\Component\Messenger\TraceableMessageHandlerInterface;
use Akeneo\Tool\Component\Messenger\TraceableMessageInterface;
use Webmozart\Assert\Assert;

final class YourMessageHandler implements TraceableMessageHandlerInterface
{
    public function __invoke(TraceableMessageInterface $message): void
    {
        Assert::isInstanceOf($message, YourMessage::class);

        // Your logic
    }
}
```

Service definition: you know how to do it ;)


- Edit the `config/events.yml` file.
You can add many queues and many consumers by queue

```yaml
queues:

    your_queue:
        message_class: Akeneo\..\YourMessage
        consumers:
            # Consumer's names are used to create the PubSub topic ID.
            # Please consult https://cloud.google.com/pubsub/docs/create-topic#resource_names
            # to check authorized characters
            - name: 'my_super_consumer_for_my_context'
              service_handler: 'your_service_handler'
            - name: 'another_consumer_for_another_context'
              service_handler: '...'

```

- Generate the deployment config

TODO JEL-226
