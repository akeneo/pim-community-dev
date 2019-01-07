<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Processor;

use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Write\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SubscriptionProcessor implements ItemProcessorInterface, InitializableInterface
{
    /** @var ProductSubscriptionRepositoryInterface */
    private $productSubscriptionRepository;

    /** @var IdentifiersMappingRepositoryInterface */
    private $identifiersMappingRepository;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var IdentifiersMapping */
    private $identifiersMapping;

    /**
     * @param ProductSubscriptionRepositoryInterface $productSubscriptionRepository
     * @param IdentifiersMappingRepositoryInterface $identifiersMappingRepository
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ProductSubscriptionRepositoryInterface $productSubscriptionRepository,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->productSubscriptionRepository = $productSubscriptionRepository;
        $this->identifiersMappingRepository = $identifiersMappingRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(): void
    {
        $this->identifiersMapping = $this->identifiersMappingRepository->find();
    }

    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        if ($product->isVariant()) {
            throw new InvalidItemException(
                'Cannot subscribe a variant product',
                new DataInvalidItem(['identifier' => $product->getIdentifier()])
            );
        }

        if (null === $product->getFamily()) {
            throw new InvalidItemException(
                'Cannot subscribe a product without family',
                new DataInvalidItem(['identifier' => $product->getIdentifier()])
            );
        }

        if (null !== $this->productSubscriptionRepository->findOneByProductId($product->getId())) {
            throw new InvalidItemException(
                'Product is already subscribed',
                new DataInvalidItem(['identifier' => $product->getIdentifier()])
            );
        }

        $fullProduct = $this->productRepository->find($product->getId());
        $request = new ProductSubscriptionRequest($fullProduct);

        if (empty($request->getMappedValues($this->identifiersMapping))) {
            throw new InvalidItemException(
                'Product does not have enough identifier values',
                new DataInvalidItem(['identifier' => $product->getIdentifier()])
            );
        }

        return $request;
    }
}
