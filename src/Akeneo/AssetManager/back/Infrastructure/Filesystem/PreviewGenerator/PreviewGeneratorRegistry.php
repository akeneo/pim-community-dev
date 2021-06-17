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

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class PreviewGeneratorRegistry implements PreviewGeneratorInterface
{
    /** @var PreviewGeneratorInterface[] */
    private ?array $previewGenerators = null;

    public const THUMBNAIL_TYPE = 'thumbnail';
    public const THUMBNAIL_SMALL_TYPE = 'thumbnail_small';
    public const PREVIEW_TYPE = 'preview';

    public function register(PreviewGeneratorInterface $previewGenerator): void
    {
        $this->previewGenerators[] = $previewGenerator;
    }

    public function supports(string $data, AbstractAttribute $attribute, string $type): bool
    {
        foreach ($this->previewGenerators as $previewGenerator) {
            if ($previewGenerator->supports($data, $attribute, $type)) {
                return true;
            }
        }

        return false;
    }

    public function generate(string $data, AbstractAttribute $attribute, string $type): string
    {
        foreach ($this->previewGenerators as $previewGenerator) {
            if ($previewGenerator->supports($data, $attribute, $type)) {
                return $previewGenerator->generate($data, $attribute, $type);
            }
        }

        throw new \RuntimeException(
            sprintf(
                'There was no generator found to get the preview of attribute "%s" with type "%s"',
                $attribute->getCode(),
                $type
            )
        );
    }

    public function remove(string $data, AbstractAttribute $attribute, string $type)
    {
        foreach ($this->previewGenerators as $previewGenerator) {
            if ($previewGenerator->supports($data, $attribute, $type)) {
                return $previewGenerator->remove($data, $attribute, $type);
            }
        }

        throw new \RuntimeException(
            sprintf(
                'There was no generator found to remove the preview of attribute "%s" with type "%s"',
                $attribute->getCode(),
                $type
            )
        );
    }
}
