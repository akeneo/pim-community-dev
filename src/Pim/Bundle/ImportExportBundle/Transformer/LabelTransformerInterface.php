<?php

namespace Pim\Bundle\ImportExportBundle\Transformer;

/**
 * Transforms a label
 *
 * @author Antoine Guigan <aguigan@qimnet.com>
 */
interface LabelTransformerInterface
{
    /**
     * Returns an array of information about a label
     *
     * The array contains the following fields
     *  - label:            the full label
     *  - name:             the raw name of the attached property
     *  - propertyPath:     the real name of the attached property
     *  - locale:           the locale of the label
     *  - scope:            the scope of the label
     *
     * @param string $label
     *
     * @return array
     */
    public function transform($label);
}
