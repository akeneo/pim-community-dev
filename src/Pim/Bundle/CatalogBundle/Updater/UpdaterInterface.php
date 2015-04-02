<?php

namespace Pim\Bundle\CatalogBundle\Updater;

use Pim\Bundle\CatalogBundle\Exception\BusinessValidationException;

/**
 * Updates and validates a business object
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface UpdaterInterface
{
    /**
     * @param mixed $object
     * @param array $data
     *
     * @return mixed the object that has been updated and validated
     *
     * @throws \InvalidArgumentException in case the object passed is not supported by the updater or data is invalid
     * @throws BusinessValidationException in case the object has validation errors
     */
    public function update($object, array $data);
}
