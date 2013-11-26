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
                $data = array(
                    'label'  => $label,
                    'locale' => null,
                    'scope'  => null,
                );
                $parts = explode('-', $data['label']);
                $data['name'] = array_shift($parts);
                $data['propertyPath'] = lcfirst(Inflector::classify($data['name']));
                if (count($parts)) {
                    $data['locale'] = array_shift($parts);
                }
                if (count($parts)) {
                    $data['scope'] = array_shift($parts);
                }

                $this->labels[$class][$label] = $data;
            }

            return $this->labels[$class][$label];
        };

        return is_array($label)
            ? array_map($transform, $label)
            : $transform($label);
    }
}
