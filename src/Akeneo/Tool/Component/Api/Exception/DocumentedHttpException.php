<?php

namespace Akeneo\Tool\Component\Api\Exception;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Add a link to a documentation in the http exception
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DocumentedHttpException extends UnprocessableEntityHttpException
{
    /** @var string */
    protected $href;

    /**
     * @param string          $href
     * @param null            $message
     * @param \Exception|null $previous
     * @param int             $code
     */
    public function __construct($href, $message = null, \Exception $previous = null, $code = 0)
    {
        parent::__construct($message, $previous, $code);

        $this->href = $href;
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->href;
    }
}
