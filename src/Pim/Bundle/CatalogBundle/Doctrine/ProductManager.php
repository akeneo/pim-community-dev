<?php
namespace Pim\Bundle\CatalogBundle\Doctrine;

use Oro\Bundle\FlexibleEntityBundle\Doctrine\FlexibleEntityManager;

/**
 * Manager of flexible product stored with doctrine
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductManager extends FlexibleEntityManager
{

    /**
     * Return shortname that can be used to get the repository or instance
     * @return string
     */
    public function getEntityShortname()
    {
        return 'PimCatalogBundle:ProductEntity';
    }

    /**
     * Return shortname that can be used to get the repository or instance
     * @return string
     */
    public function getAttributeShortname()
    {
        return 'PimCatalogBundle:ProductAttribute';
    }

    /**
     * Return shortname that can be used to get the repository or instance
     * @return string
     */
    public function getAttributeOptionShortname()
    {
        return 'PimCatalogBundle:ProductAttributeOption';
    }

    /**
     * Return shortname that can be used to get the repository or instance
     * @return string
     */
    public function getAttributeValueShortname()
    {
        return 'PimCatalogBundle:ProductAttributeValue';
    }

    /**
     * {@inheritDoc}
     */
    public function cloneEntity($entity)
    {
        $cloned = parent::cloneEntity($entity);
        $cloned->setSku($entity->getSku().str_replace('.', '', microtime(true)));

        return $cloned;
    }

}
