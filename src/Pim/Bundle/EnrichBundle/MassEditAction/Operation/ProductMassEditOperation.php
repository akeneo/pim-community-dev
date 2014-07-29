<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Base class of product mass edit operations
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class ProductMassEditOperation extends AbstractMassEditAction
{
    /**
     * {@inheritdoc}
     */
    public function affectsCompleteness()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        foreach ($this->objects as $key => $object) {
            if (!$object instanceof ProductInterface) {
                throw new \LogicException(
                    sprintf(
                        'Cannot perform mass edit action "%s" on object of type "%s", '.
                        'expecting "Pim\Bundle\CatalogBundle\Model\ProductInterface"',
                        __CLASS__,
                        get_class($object)
                    )
                );
            }

            try {
                $this->doPerform($object);
            } catch (\RuntimeException $e) {
                unset($this->objects[$key]);
            }
        }
    }

    /**
     * Return the options to use when save all products
     *
     * @return array
     */
    public function getSavingOptions()
    {
        return [
            'recalculate' => false,
            'flush'       => true,
            'schedule'    => $this->affectsCompleteness()
        ];
    }

    /**
     * Perform operation on the product instance
     *
     * @param ProductInterface $product
     *
     * @return null
     *
     * @throw \RuntimeException if operation cannot be performed on the given product
     */
    abstract protected function doPerform(ProductInterface $product);
}
