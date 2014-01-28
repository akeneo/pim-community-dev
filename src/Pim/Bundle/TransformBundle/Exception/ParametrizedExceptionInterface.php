<?php

namespace Pim\Bundle\TransformBundle\Exception;

/**
 * Interface for exception with parametrized messages
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ParametrizedExceptionInterface
{
    /**
     * Returns the message template
     *
     * @return string
     */
    public function getMessageTemplate();

    /**
     * Returns the message parameters
     *
     * @return array
     */
    public function getMessageParameters();
}
