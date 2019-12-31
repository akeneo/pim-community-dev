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
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConvention;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\Source;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;

/**
 * The goal of this class is to extract the string value of an asset given a naming convention.
 * As a reminder a naming convention can represent the code of an asset or an attribute code.
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class SourceValueExtractor
{
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
        $valueKey = ValueKey::create(
            AttributeIdentifier::fromString($source->getProperty()),
            $source->getChannelReference(),
            $source->getLocaleReference()
        );

        return $asset->findValue($valueKey);
    }

    /**
     * Try to extract string value from the data value. Most of data value can be normalized in order to
     * get the string value. If not we need to add the case manually (FileData for example).
     */
    private function extractStringDataValue(Value $value): ?string
    {
        $valueData = $value->getData();
        if ($valueData instanceof FileData) {
            return $value->getData()->getOriginalFilename();
        }

        $normalized = $valueData->normalize();

        return is_string($normalized) ? $normalized : null;
    }
}
