<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Syndication\Infrastructure\Hydrator\Value;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPriceInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValue;
use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\AssetCollectionValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\BooleanValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\DateValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\FileValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\MeasurementValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\MultiSelectValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\NullValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\NumberValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\Price;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\PriceCollectionValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\ReferenceEntityCollectionValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\ReferenceEntityValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SimpleSelectValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\StringValue;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeValueHydrator
{
    private NormalizerInterface $normalizer;

    public function __construct(
        NormalizerInterface $normalizer
    ) {
        $this->normalizer = $normalizer;
    }

    public function hydrate(
        ?ValueInterface $value,
        string $attributeType,
        $productOrProductModel
    ): SourceValueInterface {
        if (
            !($productOrProductModel instanceof ConnectorProduct ||
                $productOrProductModel instanceof ConnectorProductModel)
        ) {
            throw new \InvalidArgumentException('Cannot hydrate this entity');
        }

        $data = null !== $value ? $value->getData() : null;


        if (null === $data && 'pim_catalog_identifier' !== $attributeType) {
            return new NullValue();
        }

        $identifier = $productOrProductModel instanceof ConnectorProduct ?
            $productOrProductModel->identifier() :
            $productOrProductModel->code();

        switch ($attributeType) {
            case 'pim_catalog_asset_collection':
                return new AssetCollectionValue(
                    array_map('strval', $data),
                    $identifier,
                    $value->getScopeCode(),
                    $value->getLocaleCode()
                );
            case 'pim_catalog_file':
            case 'pim_catalog_image':
                return new FileValue(
                    $identifier,
                    $data->getStorage(),
                    $data->getKey(),
                    $data->getOriginalFilename(),
                    $value->getScopeCode(),
                    $value->getLocaleCode()
                );
            case 'pim_catalog_boolean':
                return new BooleanValue($data);
            case 'pim_catalog_date':
                return new DateValue($data);
            case 'pim_catalog_identifier':
                return $productOrProductModel instanceof ConnectorProduct ?
                    new StringValue($productOrProductModel->identifier()) :
                    new NullValue();
            case 'pim_catalog_text':
            case 'pim_catalog_textarea':
                return new StringValue($data);
            case 'pim_catalog_metric':
                if (!$value instanceof MetricValue) {
                    throw new \LogicException('Malformed value for Measurement attribute');
                }

                return new MeasurementValue($value->getAmount(), $value->getUnit());
            case 'pim_catalog_number':
                return new NumberValue((string) $data);
            case 'pim_catalog_multiselect':
                return new MultiSelectValue($data);
            case 'pim_catalog_simpleselect':
                return new SimpleSelectValue($data);
            case 'pim_catalog_price_collection':
                return new PriceCollectionValue(array_map(
                    static fn (ProductPriceInterface $price) => new Price(
                        (string) $price->getData(),
                        $price->getCurrency(),
                    ),
                    $data->toArray(),
                ));
            case 'akeneo_reference_entity':
                return new ReferenceEntityValue((string) $data);
            case 'akeneo_reference_entity_collection':
                return new ReferenceEntityCollectionValue(array_map('strval', $data));
            case 'pim_catalog_table':
                if (!$data instanceof Table) {
                    throw new \LogicException('Malformed value for Table attribute');
                }

                return new StringValue(json_encode(
                    $this->normalizer->normalize($data, 'standard'),
                    JSON_THROW_ON_ERROR,
                ));
            default:
                throw new \InvalidArgumentException(sprintf('Unsupported attribute type "%s"', $attributeType));
        }
    }
}
