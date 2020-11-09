<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception\NotGrantedCategoryException;
use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCreatedAndUpdatedEventDataBuilder implements EventDataBuilderInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $productRepository;

    /** @var NormalizerInterface */
    private $externalApiNormalizer;

    public function __construct(
        IdentifiableObjectRepositoryInterface $productRepository,
        NormalizerInterface $externalApiNormalizer
    ) {
        $this->productRepository = $productRepository;
        $this->externalApiNormalizer = $externalApiNormalizer;
    }

    /**
     * @param EventInterface|BulkEventInterface $event
     */
    public function supports(object $event): bool
    {
        // For retro-compatibility with non-bulk event.
        if ($event instanceof EventInterface) {
            return false;
        }

        foreach ($event->getEvents() as $event) {
            if (false === $event instanceof ProductCreated && false === $event instanceof ProductUpdated) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param BulkEventInterface $bulkEvent
     *
     * @throws NotGrantedCategoryException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function build(object $bulkEvent): array
    {
        if (false === $this->supports($bulkEvent)) {
            throw new \InvalidArgumentException();
        }

        $identifiers = [];

        /** @var ProductCreated|ProductUpdated $event */
        foreach ($bulkEvent->getEvents() as $event) {
            $identifiers[] = $event->getIdentifier();
        }

        /*
        try {
            $product = $this->productRepository->($event->getIdentifier());
            if (null === $product) {
                throw new ProductNotFoundException($event->getIdentifier());
            }
        } catch (AccessDeniedException $e) {
            throw new NotGrantedCategoryException($e->getMessage(), $e);
        }

        return [
            'resource' => $this->externalApiNormalizer->normalize($product, 'external_api'),
        ];
        */

        return \array_map(function ($identifier) {
            return ['resource' => ['identifier' => $identifier]];
        }, $identifiers);
    }
}
