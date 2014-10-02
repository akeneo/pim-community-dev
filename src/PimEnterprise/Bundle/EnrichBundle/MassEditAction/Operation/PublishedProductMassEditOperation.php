<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation;

use Pim\Bundle\EnrichBundle\MassEditAction\Operation\AbstractMassEditAction;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface;

/**
 * Base class of published product mass edit operations
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
abstract class PublishedProductMassEditOperation extends AbstractMassEditAction
{
    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        foreach ($this->objects as $object) {
            if (!$object instanceof PublishedProductInterface) {
                throw new \LogicException(
                    sprintf(
                        'Cannot perform mass edit action "%s" on object of type "%s", '.
                        'expecting "PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface"',
                        __CLASS__,
                        get_class($object)
                    )
                );
            }
            $this->doPerform($object);
        }
    }

    /**
     * Perform operation on the published product instance
     *
     * @param PublishedProductInterface $published
     */
    abstract protected function doPerform(PublishedProductInterface $published);
}
