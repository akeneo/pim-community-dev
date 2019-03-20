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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Elasticsearch\Indexing\Normalizer;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Query\Product\ProductSubscriptionsExistQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class ProductSubscriptionNormalizer implements NormalizerInterface
{
    /** @var ProductSubscriptionsExistQueryInterface */
    private $productSubscriptionsExistQuery;

    public function __construct(ProductSubscriptionsExistQueryInterface $productSubscriptionsExistQuery)
    {
        $this->productSubscriptionsExistQuery = $productSubscriptionsExistQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $productsSubscribedToFranklin = $this->productSubscriptionsExistQuery->execute([$object->getId()]);

        return ['franklin_subscription' => $productsSubscribedToFranklin[$object->getId()]];
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
