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

namespace Akeneo\ReferenceEntity\Infrastructure\PreviewGenerator;

use Akeneo\Pim\Enrichment\Bundle\File\DefaultImageProviderInterface;
use Akeneo\Pim\Enrichment\Bundle\File\FileTypes;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\Url\MediaType;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\UrlAttribute;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class OtherGenerator implements PreviewGeneratorInterface
{
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
            && in_array($type,PreviewGeneratorRegistry::SUPPORTED_TYPES);
    }

    public function generate(string $data, AbstractAttribute $attribute, string $type): string
    {
        return $this->defaultImageProvider->getImageUrl(FileTypes::MISC, $type);
    }
}
