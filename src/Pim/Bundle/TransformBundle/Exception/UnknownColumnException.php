<?php

namespace Pim\Bundle\TransformBundle\Exception;

/**
 * Exception thrown when a column is unknown
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UnknownColumnException extends ParametrizedException
{
    /**
     * @var array
     */
    public $labels;

    /**
     * Constructor
     *
     * @param array  $labels
     * @param string $class
     */
    public function __construct(array $labels, $class)
    {
        parent::__construct(
            'Columns [%labels%] do not exist on class [%class%].',
            array('%labels%' => implode(', ', $labels), '%class%' => $class)
        );
    }
}
