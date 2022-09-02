<?php

namespace Akeneo\SharedCatalog\Query;

use Akeneo\Pim\Enrichment\Product\API\Query\GetProductUuidsQuery;
use Akeneo\SharedCatalog\Model\SharedCatalog;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Webmozart\Assert\Assert;

class FindProductUuidsQuery implements FindProductUuidsQueryInterface
{
    public function __construct(private MessageBusInterface $messageBus)
    {
    }

    public function find(SharedCatalog $sharedCatalog, $options = []): array
    {
        $options = $this->resolveOptions($options);

        $searchAfterProductUuid = $options['search_after'];
        if (null !== $searchAfterProductUuid) {
            $searchAfterProductUuid = Uuid::fromString($searchAfterProductUuid);
        }
        $envelope = $this->messageBus->dispatch(new GetProductUuidsQuery([], null, $searchAfterProductUuid));

        $handledStamp = $envelope->last(HandledStamp::class);
        Assert::notNull($handledStamp, 'The bus does not return any result');

        $productUuidCursor = $handledStamp->getResult();

        $productUuids = [];
        foreach ($productUuidCursor as $productUuid) {
            $productUuids[] = $productUuid->toString();
            if (count($productUuids) >= $options['limit']) {
                return $productUuids;
            }
        }

        return $productUuids;
    }

    private function resolveOptions(array $options = []): array
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired([
            'limit',
        ]);
        $resolver->setDefined([
            'search_after',
        ]);
        $resolver->setDefaults([
            'search_after' => null,
        ]);
        $resolver->setAllowedTypes('search_after', ['string', 'null']);
        $resolver->setAllowedTypes('limit', 'int');

        return $resolver->resolve($options);
    }
}
