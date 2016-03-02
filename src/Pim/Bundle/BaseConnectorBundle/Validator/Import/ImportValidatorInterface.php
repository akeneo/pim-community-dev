<?php

namespace Pim\Bundle\BaseConnectorBundle\Validator\Import;

/**
 * Validates an imported entity
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be remove in 1.6
 */
interface ImportValidatorInterface
{
    /**
     * Validates an entity and returns an array of errors
     *
     * @param type  $entity
     * @param array $columnsInfo
     * @param array $data
     * @param array $errors
     *
     * @return array
     */
    public function validate($entity, array $columnsInfo, array $data, array $errors = []);
}
