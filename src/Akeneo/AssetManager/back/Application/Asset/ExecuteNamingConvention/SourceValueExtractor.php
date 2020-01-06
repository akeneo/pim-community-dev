<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\MediaLinkData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\NumberData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\OptionData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\TextData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConvention;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\Source;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
use Akeneo\AssetManager\Domain\Repository\AttributeNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;

/**
 * The goal of this class is to extract the string value of an asset given a naming convention.
 * As a reminder a naming convention can represent the code of an asset or an attribute code.
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class SourceValueExtractor
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function extract(Asset $asset, NamingConvention $namingConvention): ?string
    {
        $source = $namingConvention->getSource();
        if ($source->isAssetCode()) {
            return $asset->getCode()->__toString();
        }

        $value = $this->findValueInAsset($asset, $source);

        return null === $value ? null : $this->extractStringDataValue($value);
    }

    private function findValueInAsset(Asset $asset, Source $source): ?Value
    {
        try {
            $attribute = $this->attributeRepository->getByCodeAndAssetFamilyIdentifier(
                AttributeCode::fromString($source->getProperty()),
                $asset->getAssetFamilyIdentifier()
            );
        } catch (AttributeNotFoundException $e) {
            return null;
        }

        $valueKey = ValueKey::create(
            $attribute->getIdentifier(),
            $source->getChannelReference(),
            $source->getLocaleReference()
        );

        return $asset->findValue($valueKey);
    }

    /**
     * We can extract string value only from "FileData" attribute.
     */
    private function extractStringDataValue(Value $value): ?string
    {
        $valueData = $value->getData();
        if ($valueData instanceof FileData) {
            return $valueData->getOriginalFilename();
        }

        // @todo AST-205: handle error (or just return null?)
        return null;
    }
}
