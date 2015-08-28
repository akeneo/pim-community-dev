<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Component\FileTransformer\Exception\NotApplicableTransformation;

/**
 * Exception thrown when Transformation is not applicable
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class ImageHeightException extends GenericTransformationException
{
    /**
     * @param string $image
     * @param string $transformation
     */
    public function __construct($image, $transformation)
    {
        $message = sprintf(
            'Impossible to "%s" the image "%s" with a height bigger than the original.',
            $transformation,
            $image
        );

        parent::__construct($message);
    }
}
