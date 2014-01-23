<?php

namespace Pim\Bundle\ImportExportBundle\Validator\Import;

/**
 * Empty validator
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SkipImportValidator implements ImportValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function validate($entity, array $columnsInfo, array $data, array $errors = [])
    {
        return [];
    }
}
