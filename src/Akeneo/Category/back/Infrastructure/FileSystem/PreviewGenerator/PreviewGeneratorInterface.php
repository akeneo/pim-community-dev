<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\FileSystem\PreviewGenerator;

use Akeneo\Category\Domain\Model\Attribute\Attribute;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface PreviewGeneratorInterface
{
    public function supports(string $data, Attribute $attribute, string $type): bool;

    public function supportsMimeType(string $mimeType): bool;

    /**
     * @param string $data The filename of the external image we want to generate (ex : akeneo.jpg)
     * @param Attribute $attribute The attribute which need to have a preview
     * @param string $type The format type used to generate the image (ex : dam_thumbnail, dam_preview)
     *
     * @return string Return the URL of the preview generated
     */
    public function generate(string $data, Attribute $attribute, string $type): string;

    /**
     * @param string $data The filename of the external image we want to generate (ex : akeneo.jpg)
     * @param Attribute $attribute The attribute which need to have a preview
     * @param string $type The format type used to generate the image (ex : dam_thumbnail, dam_preview)
     */
    public function remove(string $data, string $type);
}
