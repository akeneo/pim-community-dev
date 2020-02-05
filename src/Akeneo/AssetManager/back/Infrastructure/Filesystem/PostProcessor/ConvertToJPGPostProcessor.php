<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Filesystem\PostProcessor;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Filter\PostProcessor\PostProcessorInterface;
use Liip\ImagineBundle\Model\Binary;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class ConvertToJPGPostProcessor implements PostProcessorInterface
{
    public const MIME_TYPE = 'image/jpeg';

    public function process(BinaryInterface $binary, array $options = []): BinaryInterface
    {
        $image = new \Imagick();
        $image->readImageBlob($binary->getContent());
        $image->setImageCompressionQuality($options['quality']);
        $isSuccess = $image->setImageFormat('jpeg');

        if ($isSuccess) {
            return new Binary($image->__toString(), static::MIME_TYPE);
        }

        return $binary;
    }
}
