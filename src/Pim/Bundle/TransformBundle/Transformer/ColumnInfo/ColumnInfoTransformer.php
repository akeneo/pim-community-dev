<?php

namespace Pim\Bundle\TransformBundle\Transformer\ColumnInfo;

/**
 * Transforms a label
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ColumnInfoTransformer implements ColumnInfoTransformerInterface
{
    /**
     * @var string
     */
    protected $columnInfoClass;

    /**
     * Constructor
     * @param string $columnInfoClass
     */
    public function __construct($columnInfoClass)
    {
        $this->columnInfoClass = $columnInfoClass;
    }
    /**
     * @var array
     */
    protected $labels = array();

    /**
     * {@inheritdoc}
     */
    public function transform($class, $label)
    {
        $transform = function ($label) use ($class) {
            if (!isset($this->labels[$class][$label])) {
                if (!isset($this->labels[$class])) {
                    $this->labels[$class] = array();
                }
                $this->labels[$class][$label] = new $this->columnInfoClass($label);
            }

            return $this->labels[$class][$label];
        };

        return is_array($label)
            ? array_map($transform, $label)
            : $transform($label);
    }
}
