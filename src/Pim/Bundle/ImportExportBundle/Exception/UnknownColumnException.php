<?php

namespace Pim\Bundle\ImportExportBundle\Exception;

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
     * @param array $labels
     */
    public function __construct(array $labels)
    {
        parent::__construct(
            'Columns %labels% do not exist.',
            ['%labels%' => implode(', ', $labels)]
        );
    }
}
