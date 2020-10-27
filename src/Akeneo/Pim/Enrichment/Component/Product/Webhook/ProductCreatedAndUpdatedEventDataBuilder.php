<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception\ProductNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception\NotGrantedCategoryException;
use Akeneo\Platform\Component\EventQueue\BusinessEventInterface;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
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

    public function supports(BusinessEventInterface $businessEvent): bool
    {
        return $businessEvent instanceof ProductUpdated || $businessEvent instanceof ProductCreated;
    }

    /**
     * @param ProductCreated|ProductUpdated $businessEvent
     * @throws NotGrantedCategoryException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function build(BusinessEventInterface $businessEvent): array
    {
        if (false === $this->supports($businessEvent)) {
            throw new \InvalidArgumentException();
        }

        $data = $businessEvent->data();

        try {
            $product = $this->productRepository->findOneByIdentifier($data['identifier']);
        } catch (AccessDeniedException $e) {
            throw new NotGrantedCategoryException($e->getMessage(), $e);
        }

        if (null === $product) {
            throw new ProductNotFoundException($data['identifier']);
        }

        return [
            'resource' => $this->externalApiNormalizer->normalize($product, 'external_api'),
        ];
    }
}
