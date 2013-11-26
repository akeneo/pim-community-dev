<?php

namespace Pim\Bundle\ImportExportBundle\Transformer;


/**
 * Represents Column information
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ColumnInfo extends \ArrayObject
{
    public function __construct($label)
    {
        $data = array(
            'label'  => $label,
        );
        $parts = explode('-', $data['label']);
        $data['name'] = array_shift($parts);
        $data['propertyPath'] = lcfirst(Inflector::classify($data['name']));
        $data['suffixes'] = $parts;
        parent::__construct($data);
    }
}
