<?php

namespace Pim\Component\Localization\Exception;

/**
 * Exception thrown from localizer
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FormatLocalizerException extends \Exception
{
    /***
     * @param string     $attribute
     * @param string     $option
     * @param string     $message
     * @param int        $code
     * @param \Exception $previous
     */
    public function __construct(
        $attribute,
        $option,
        $message = 'Format for attribute "%s" is not respected. Format expected: "%s"',
        $code = 0,
        \Exception $previous = null
    ) {
        $message = sprintf($message, $attribute, $option);

        return parent::__construct($message, $code, $previous);
    }
}
