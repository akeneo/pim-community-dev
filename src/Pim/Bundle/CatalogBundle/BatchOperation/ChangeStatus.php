<?php

namespace Pim\Bundle\CatalogBundle\BatchOperation;

use Pim\Bundle\CatalogBundle\Form\Type\BatchOperation\ChangeStatusType;

/**
 * Batch operation to change products status
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChangeStatus implements BatchOperation
{
    protected $enable = true;

    public function getFormType()
    {
        return new ChangeStatusType();
    }

    public function setEnable($enable)
    {
        $this->enable = $enable;

        return $this;
    }

    public function getEnable()
    {
        return $this->enable;
    }
}
