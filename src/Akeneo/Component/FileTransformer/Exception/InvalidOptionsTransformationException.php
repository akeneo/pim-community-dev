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

use Akeneo\Component\FileTransformer\Exception\NotApplicableTransformation\GenericTransformationException;

/**
 * Exception thrown when Transformation options are invalid
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class InvalidOptionsTransformationException extends GenericTransformationException
{
    /**
     * @param \Exception $e
     * @param string     $transformation
     *
     * @return InvalidOptionsTransformationException
     */
    public static function general(\Exception $e, $transformation)
    {
        return new self(
            sprintf('Your options does not fulfil the requirements of the "%s" transformation.', $transformation),
            $e->getCode(),
            $e
        );
    }

    /**
     * @param array  $options
     * @param string $transformation
     *
     * @return InvalidOptionsTransformationException
     */
    public static function chooseOneOption(array $options, $transformation)
    {
        $options = '"' . implode('", "', $options) . '"';

        return new self(
            sprintf('Please choose one of the among option %s for the "%s" transformation.', $options, $transformation)
        );
    }

    /**
     * @param string $option
     * @param string $transformation
     *
     * @return InvalidOptionsTransformationException
     */
    public static function ratio($option, $transformation)
    {
        return new self(
            sprintf('The option "%s" of the "%s" transformation should be between 0 and 100.', $option, $transformation)
        );
    }
}
