<?php

namespace Pim\Bundle\ImportExportBundle\Validator\Csv;

/**
 * Validates a csv attribute row
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeValidator extends AbstractRowValidator
{
    /**
     * @var array
     */
    protected $notBlankFields = array('type', 'code');

    /**
     * @var array
     */
    protected $booleanFields = array(
        'unique', 'useable_as_grid_column', 'useable_as_grid_filter', 'is_translatable', 'is_scopable'
    );
}
