<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Component\FileTransformer\Exception;

/**
 * Exception thrown when Transformation is not applicable due to a bad option value
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class NotApplicableTransformationException extends \Exception
{
    /**
     * @param string $image
     * @param string $transformation
     *
     * @return NotApplicableTransformationException
     */
    public static function imageWidthTooBig($image, $transformation)
    {
        return new self(
            sprintf('Impossible to "%s" the image "%s" with a width bigger than the original.', $transformation, $image)
        );
    }

    /**
     * @param string $image
     * @param string $transformation
     *
     * @return NotApplicableTransformationException
     */
    public static function imageHeightTooBig($image, $transformation)
    {
        return new self(
            sprintf(
                'Impossible to "%s" the image "%s" with a height bigger than the original.',
                $transformation,
                $image
            )
        );
    }
}
