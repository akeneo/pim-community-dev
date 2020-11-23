<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception\NotGrantedCategoryException;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception\ProductModelNotFoundException;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use Akeneo\Platform\Component\Webhook\EventDataCollection;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelCreatedAndUpdatedEventDataBuilder implements EventDataBuilderInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $productModelRepository;

    /** @var NormalizerInterface */
    private $externalApiNormalizer;

    public function __construct(
        IdentifiableObjectRepositoryInterface $productModelRepository,
        NormalizerInterface $externalApiNormalizer
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->externalApiNormalizer = $externalApiNormalizer;
    }

    public function supports(object $event): bool
    {
        return $event instanceof ProductModelUpdated || $event instanceof ProductModelCreated;
    }

    /**
     * @param ProductModelUpdated|ProductModelCreated $event
     */
    public function build(object $event, UserInterface $user): EventDataCollection
    {
        if (false === $this->supports($event)) {
            throw new \InvalidArgumentException();
        }

        try {
            $productModel = $this->productModelRepository->findOneByIdentifier($event->getCode());
        } catch (AccessDeniedException $e) {
            throw new NotGrantedCategoryException($e->getMessage(), $e);
        }

        if (null === $productModel) {
            throw new ProductModelNotFoundException($event->getCode());
        }

        return (new EventDataCollection())->setEventData($event, [
            'resource' => $this->externalApiNormalizer->normalize($productModel, 'external_api'),
        ]);
    }
}
