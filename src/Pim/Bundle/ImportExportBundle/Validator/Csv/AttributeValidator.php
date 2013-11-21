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
     * Get constraints to apply on each field
     *
     * @return array
     */
    protected function getFieldConstraints()
    {
        if (empty($this->constraints)) {
            $notBlankFields = array('type', 'code');
            foreach ($notBlankFields as $field) {
                $this->constraints[$field] = array($this->buildNotBlankConstraint($field));
            }
            $booleanFields = array(
                'unique', 'useable_as_grid_column', 'useable_as_grid_filter', 'is_translatable', 'is_scopable'
            );
            foreach ($booleanFields as $field) {
                $this->constraints[$field] = array($this->buildBooleanConstraint($field));
            }
        }

        return $this->constraints;
    }
}
