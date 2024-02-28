<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\FileSystem\PreviewGenerator;

use Liip\ImagineBundle\Model\Binary;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface DefaultImageProviderInterface
{
    /**
     * Return the url of the default image corresponding to the specified file type.
     *
     * @param $fileType string type, defined in Akeneo\Pim\Enrichment\Bundle\File\FileTypes
     * @param $filter string filter name
     */
    public function getImageUrl(string $fileType, string $filter): string;

    /**
     * Return a Binary instance that embed the image corresponding to the specified file type.
     *
     * @throws \InvalidArgumentException
     */
    public function getImageBinary(string $fileType): Binary;
}
