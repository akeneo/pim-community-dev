<?php

namespace Pim\Bundle\ImportExportBundle\Transformer;

use Doctrine\Common\Inflector\Inflector;

/**
 * Transforms a label
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LabelTransformer implements LabelTransformerInterface
{
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
                $this->labels[$class][$label] = new ColumnInfo($label);
            }

            return $this->labels[$class][$label];
        };

        return is_array($label)
            ? array_map($transform, $label)
            : $transform($label);
    }
}
