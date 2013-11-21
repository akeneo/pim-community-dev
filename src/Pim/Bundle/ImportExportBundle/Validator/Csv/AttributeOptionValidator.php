<?php

namespace Pim\Bundle\ImportExportBundle\Validator\Csv;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * Validates a csv option row
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionValidator extends AbstractValidator
{
    /**
     * Get constraints to apply on each field
     *
     * @return array
     */
    protected function getFieldConstraints()
    {
        if (empty($this->constraints)) {
            $notBlank = new NotBlank(array('message' => 'The value attribute should not be blank.'));
            $this->constraints['attribute'] = array($notBlank);

            $notBlank = new NotBlank(array('message' => 'The value code should not be blank.'));
            $this->constraints['code'] = array($notBlank);
            $authorized = new Regex(
                array(
                    'pattern' => '/^[0-1]$/',
                    'message' => 'The value is_default must be 0 or 1.'
                )
            );
            $this->constraints['is_default'] = array($authorized);
        }

        return $this->constraints;
    }
}
