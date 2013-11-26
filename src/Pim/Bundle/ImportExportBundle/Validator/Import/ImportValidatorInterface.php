<?php

namespace Pim\Bundle\ImportExportBundle\Validator\Import;

/**
 * Description of ImportValidatorInterface
 *
 * @author Antoine Guigan <aguigan@qimnet.com>
 */
interface ImportValidatorInterface
{
    public function validate($entity, array $data, array $errors = array());
}
