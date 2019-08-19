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

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Factory;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetMultipleLinkType;
use Akeneo\Pim\Enrichment\AssetManager\Component\Value\AssetMultipleLinkValue;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Write\Value\AbstractValueFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * Factory that creates asset family product values.
 *
 * @internal  Please, do not use this class directly. You must use \Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory.
 *
 * @author    Julien Sanchez (julien@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class AssetMultipleLinkValueFactory extends AbstractValueFactory
{
    /** @var AssetRepositoryInterface */
    private $assetRepository;

    /**
     * @param AssetRepositoryInterface $assetRepository
     */
    public function __construct(AssetRepositoryInterface $assetRepository)
    {
        parent::__construct(
            AssetMultipleLinkValue::class,
            AssetMultipleLinkType::ASSET_MULTIPLE_LINK
        );

        $this->assetRepository = $assetRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareData(AttributeInterface $attribute, $data, bool $ignoreUnknownData)
    {
        if (null === $data) {
            $data = [];
        }

        if (!is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected(
                $attribute->getCode(),
                static::class,
                $data
            );
        }

        foreach ($data as $key => $value) {
            if (!is_string($value)) {
                throw InvalidPropertyTypeException::validArrayStructureExpected(
                    $attribute->getCode(),
                    sprintf('array key "%s" expects a string as value, "%s" given', $key, gettype($value)),
                    static::class,
                    $data
                );
            }
        }

        return $this->getAssetCodeCollection($attribute, $data);
    }

    /**
     * Gets a collection of asset code object from an array of string codes
     *
     * @throws InvalidPropertyTypeException
     */
    protected function getAssetCodeCollection(AttributeInterface $attribute, array $assetCodes): array
    {
        $collection = [];

        foreach ($assetCodes as $code) {
            $assetFamilyIdentifier = $attribute->getReferenceDataName();
            $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($assetFamilyIdentifier);
            $assetCode = AssetCode::fromString($code);

            try {
                $asset = $this->assetRepository->getByAssetFamilyAndCode($assetFamilyIdentifier, $assetCode);
            } catch (AssetNotFoundException $e) {
                // The asset has been removed, we can go on and continue to load the rest of the assets.

                continue;
            }

            if (!in_array($asset->getCode(), $collection, true)) {
                $collection[] = $asset->getCode();
            }
        }

        return $collection;
    }
}
