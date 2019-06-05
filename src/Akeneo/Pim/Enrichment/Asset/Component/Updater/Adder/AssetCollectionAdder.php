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

namespace Akeneo\Pim\Enrichment\Asset\Component\Updater\Adder;

use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AbstractAttributeAdder;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataCollectionValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * Value adder for assets collection attributes
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AssetCollectionAdder extends AbstractAttributeAdder
{
    /**
     * @param EntityWithValuesBuilderInterface $entityWithValuesBuilder
     * @param array                            $supportedTypes
     */
    public function __construct(EntityWithValuesBuilderInterface $entityWithValuesBuilder, array $supportedTypes)
    {
        parent::__construct($entityWithValuesBuilder);

        $this->supportedTypes = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     *
     * Expected data input format: ["asset_code_1", "asset_code_2"]
     */
    public function addAttributeData(
        EntityWithValuesInterface $entityWithValues,
        AttributeInterface $attribute,
        $data,
        array $options = []
    ): void {
        $options = $this->resolver->resolve($options);

        if (!is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected(
                $attribute->getCode(),
                static::class,
                $data
            );
        }

        $this->addAssetCodes($entityWithValues, $attribute, $data, $options['locale'], $options['scope']);
    }

    private function addAssetCodes($entityWithValues, $attribute, $assetCodes, $locale, $scope)
    {
        /** @var ReferenceDataCollectionValue $assetCollectionValue */
        $assetCollectionValue = $entityWithValues->getValue($attribute->getCode(), $locale, $scope);

        if (null !== $assetCollectionValue) {
            foreach ($assetCollectionValue->getReferenceDataCodes() as $refDataCode) {
                if (!in_array($refDataCode, $assetCodes)) {
                    $assetCodes[] = $refDataCode;
                }
            }
        }

        $this->entityWithValuesBuilder->addOrReplaceValue($entityWithValues, $attribute, $locale, $scope, $assetCodes);
    }
}
