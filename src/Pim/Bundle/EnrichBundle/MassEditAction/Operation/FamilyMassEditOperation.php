<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Pim\Bundle\CatalogBundle\Model\FamilyInterface;

/**
 * Base class of family mass edit operations
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class FamilyMassEditOperation extends AbstractMassEditAction
{
    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        foreach ($this->objects as $object) {
            if (!$object instanceof FamilyInterface) {
                throw new \LogicException(
                    sprintf(
                        'Cannot perform mass edit action "%s" on object of type "%s", '.
                        'expecting "Pim\Bundle\CatalogBundle\Model\FamilyInterface"',
                        __CLASS__,
                        get_class($object)
                    )
                );
            }
            $this->doPerform($object);
        }
    }

    /**
     * Perform operation on the product instance
     *
     * @param FamilyInterface $family
     */
    abstract protected function doPerform(FamilyInterface $family);
}
