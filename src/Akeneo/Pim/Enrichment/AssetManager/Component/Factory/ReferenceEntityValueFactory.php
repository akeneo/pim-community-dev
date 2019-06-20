<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\AbstractValueFactory;
use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetSingleLinkType;
use Akeneo\Pim\Enrichment\AssetManager\Component\Value\AssetSingleLinkValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * Factory that creates asset family product values.
 *
 * @internal  Please, do not use this class directly. You must use \Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class AssetSingleLinkValueFactory extends AbstractValueFactory
{
    /** @var AssetRepositoryInterface */
    private $assetRepository;

    public function __construct(AssetRepositoryInterface $assetRepository)
    {
        parent::__construct(AssetSingleLinkValue::class, AssetSingleLinkType::ASSET_SINGLE_LINK);

        $this->assetRepository = $assetRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareData(AttributeInterface $attribute, $data, bool $ignoreUnknownData)
    {
        if (null === $data) {
            return;
        }

        if (!is_string($data)) {
            throw InvalidPropertyTypeException::stringExpected(
                $attribute->getCode(),
                static::class,
                $data
            );
        }

        return $this->getAssetCode($attribute, $data);
    }

    /**
     * Gets the Asset code (or null) for the given $code
     */
    private function getAssetCode(AttributeInterface $attribute, $code): ?AssetCode
    {
        if (null === $code) {
            return null;
        }

        $assetFamilyIdentifier = $attribute->getReferenceDataName();
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($assetFamilyIdentifier);
        $assetCode = AssetCode::fromString($code);

        try {
            $asset = $this->assetRepository->getByAssetFamilyAndCode($assetFamilyIdentifier, $assetCode);
        } catch (AssetNotFoundException $e) {
            // The asset has been removed, we don't crash the app but set asset to null.
            $asset = null;
        }

        if (null === $asset) {
            return null;
        }

        return $asset->getCode();
    }
}
