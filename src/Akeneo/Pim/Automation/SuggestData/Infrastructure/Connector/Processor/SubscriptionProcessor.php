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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Connector\Processor;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SubscriptionProcessor implements ItemProcessorInterface
{
    /** @var ProductSubscriptionRepositoryInterface */
    private $productSubscriptionRepository;

    /** @var IdentifiersMapping */
    private $identifiersMapping;

    /**
     * @param ProductSubscriptionRepositoryInterface $productSubscriptionRepository
     * @param IdentifiersMappingRepositoryInterface $identifiersMappingRepository
     */
    public function __construct(
        ProductSubscriptionRepositoryInterface $productSubscriptionRepository,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository
    ) {
        $this->productSubscriptionRepository = $productSubscriptionRepository;
        $this->identifiersMapping = $identifiersMappingRepository->find();
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

        $request = new ProductSubscriptionRequest($product);
        if (empty($request->getMappedValues($this->identifiersMapping))) {
            throw new InvalidItemException(
                'Product does not have enough identifier values',
                new DataInvalidItem(['identifier' => $product->getIdentifier()])
            );
        }

        return $request;
    }
}
