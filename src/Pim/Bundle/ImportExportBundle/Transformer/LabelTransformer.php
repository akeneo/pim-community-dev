<?php

namespace Pim\Bundle\ImportExportBundle\Transformer;

use Doctrine\Common\Inflector\Inflector;

/**
 * Transforms a label
 *
 * @author Antoine Guigan <aguigan@qimnet.com>
 */
class LabelTransformer implements LabelTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($label)
    {
        $data = array(
            'label'  => $label,
            'locale' => null,
            'scope'  => null,
        );
        $parts = explode('-', $label);
        $data['name'] = array_shift($parts);
        $data['propertyPath'] = lcfirst(Inflector::classify($data['name']));
        if (count($parts) > 1) {
            $data['scope'] = array_shift($parts);
        }
        if (count($parts)) {
            $data['locale'] = array_shift($parts);
        }

        return $data;
    }
}
