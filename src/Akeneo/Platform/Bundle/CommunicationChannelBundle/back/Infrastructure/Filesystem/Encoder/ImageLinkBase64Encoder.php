<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Infrastructure\Filesystem\Encoder;

use League\Flysystem\Util\MimeType;

/**
 * @author Christophe Chausseray <chaauseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ImageLinkBase64Encoder
{
    public static function encode(string $imageLink): string
    {
        $imageFile = file_get_contents($imageLink);
        $mimeType = MimeType::detectByFilename($imageLink);

        if (false === $imageFile || (null !== $mimeType && false === preg_match('/image\/*/', $mimeType))) {
            return '';
        }

        return base64_encode($imageFile);
    }
}
