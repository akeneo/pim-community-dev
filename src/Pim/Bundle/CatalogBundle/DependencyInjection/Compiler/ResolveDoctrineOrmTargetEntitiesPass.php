<?php

namespace Pim\Bundle\CatalogBundle\DependencyInjection\Compiler;

/**
 * Resolves doctrine ORM Target entities
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResolveDoctrineOrmTargetEntitiesPass extends AbstractResolveDoctrineOrmTargetEntitiesPass
{
    /**
     * {@inheritdoc}
     */
    protected function getParametersMapping()
    {
        return array(
            'Symfony\Component\Security\Core\User\UserInterface'   => 'oro_user.entity.class',
            'Pim\Bundle\CatalogBundle\Model\ProductValueInterface' => 'pim_catalog.entity.product_value.class',
            'Pim\Bundle\CatalogBundle\Model\ProductInterface'      => 'pim_catalog.entity.product.class',
            'Pim\Bundle\CatalogBundle\Model\CategoryInterface'     => 'pim_catalog.entity.category.class',
        );
    }
}
