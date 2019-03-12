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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Indexing\Normalizer;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Query\Product\IsProductSubscribedToFranklinQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class ProductSubscriptionNormalizer implements NormalizerInterface
{
    /** @var IsProductSubscribedToFranklinQueryInterface */
    private $isProductSubscribedToFranklinQuery;

    public function __construct(IsProductSubscribedToFranklinQueryInterface $isProductSubscribedToFranklinQuery)
    {
        $this->isProductSubscribedToFranklinQuery = $isProductSubscribedToFranklinQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $isProductSubscribedToFranklin = $this->isProductSubscribedToFranklinQuery->execute($object->getId());

        return ['franklin_subscription' => $isProductSubscribedToFranklin];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface
            && ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX === $format;
    }
}
