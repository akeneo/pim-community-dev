<?php

namespace Pim\Bundle\ImportExportBundle\Validator\Csv;

/**
 * Validates a csv option row
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionValidator extends AbstractRowValidator
{
    /**
     * @var array
     */
    protected $notBlankFields = array('attribute', 'code');

    /**
     * @var array
     */
    protected $booleanFields = array('is_default');
}
