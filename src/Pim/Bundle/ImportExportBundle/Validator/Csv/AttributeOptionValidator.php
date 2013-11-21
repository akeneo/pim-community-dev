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
     * Get constraints to apply on each field
     *
     * @return array
     */
    protected function getFieldConstraints()
    {
        if (empty($this->constraints)) {
            $notBlankFields = array('code', 'attribute');
            foreach ($notBlankFields as $field) {
                $this->constraints[$field] = array($this->buildNotBlankConstraint($field));
            }
            $booleanFields = array('is_default');
            foreach ($booleanFields as $field) {
                $this->constraints[$field] = array($this->buildBooleanConstraint($field));
            }
        }

        return $this->constraints;
    }
}
