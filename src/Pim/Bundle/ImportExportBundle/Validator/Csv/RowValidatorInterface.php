<?php

namespace Pim\Bundle\ImportExportBundle\Validator\Csv;

/**
 * Validates a csv row
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface RowValidatorInterface
{
    /**
     * @param array $data
     *
     * @return array an array of errors
     */
    public function validate(array $data);
}
