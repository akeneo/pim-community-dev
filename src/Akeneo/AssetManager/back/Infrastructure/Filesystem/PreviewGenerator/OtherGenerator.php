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

namespace Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator;

use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\Url\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\UrlAttribute;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class OtherGenerator implements PreviewGeneratorInterface
{
    private const DEFAULT_OTHER = 'pim_asset_file_other';

    /** @var DefaultImageProviderInterface  */
    private $defaultImageProvider;

    public function __construct(DefaultImageProviderInterface $defaultImageProvider)
    {
        $this->defaultImageProvider = $defaultImageProvider;
    }

    public function supports(string $data, AbstractAttribute $attribute, string $type): bool
    {
        return UrlAttribute::ATTRIBUTE_TYPE === $attribute->getType()
            && MediaType::OTHER === $attribute->getMediaType()->normalize()
            && in_array($type, PreviewGeneratorRegistry::SUPPORTED_TYPES);
    }

    public function generate(string $data, AbstractAttribute $attribute, string $type): string
    {
        return $this->defaultImageProvider->getImageUrl(self::DEFAULT_OTHER, $type);
    }
}
