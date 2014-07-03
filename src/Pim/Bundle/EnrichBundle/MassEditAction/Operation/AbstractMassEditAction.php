<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

/**
 * Class that Batch operations might extend for convenience purposes
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractMassEditAction implements MassEditOperationInterface
{
    /** @var array */
    protected $objects;

    /**
     * {@inheritdoc}
     */
    public function getFormOptions()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
    }

    /**
     * Set objects to mass edit
     *
     * @param array $objects
     *
     * @return MassEditOperationInterface
     */
    public function setObjectsToMassEdit(array $objects)
    {
        $this->objects = $objects;

        return $this;
    }

    /**
     * @return array
     */
    public function getObjectsToMassEdit()
    {
        return $this->objects;
    }
}
