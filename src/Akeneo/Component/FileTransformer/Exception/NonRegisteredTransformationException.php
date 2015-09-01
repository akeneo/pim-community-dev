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
 * Exception thrown when a Transformation is not registered in the registry
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class NonRegisteredTransformationException extends \Exception
{
    /** @var string */
    protected $transformation;

    /** @var string */
    protected $mimeType;

    /**
     * @param string     $transformation
     * @param string     $mimeType
     * @param string     $message
     * @param int        $code
     * @param \Exception $previous
     */
    public function __construct($transformation, $mimeType, $message = '', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->transformation = $transformation;
        $this->mimeType = $mimeType;
    }

    /**
     * @return string
     */
    public function getTransformation()
    {
        return $this->transformation;
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }
}
