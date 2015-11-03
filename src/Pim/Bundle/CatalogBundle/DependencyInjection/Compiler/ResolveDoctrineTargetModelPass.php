<?php

namespace Pim\Bundle\CatalogBundle\DependencyInjection\Compiler;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\AbstractResolveDoctrineTargetModelPass;

/**
 * Resolves doctrine ORM Target entities
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResolveDoctrineTargetModelPass extends AbstractResolveDoctrineTargetModelPass
{
    /**
     * {@inheritdoc}
     */
    protected function getParametersMapping()
    {
        return [
            'Symfony\Component\Security\Core\User\UserInterface'      => 'oro_user.entity.class',
            'Pim\Component\Catalog\Model\AssociationInterface'     => 'pim_catalog.entity.association.class',
            'Pim\Component\Catalog\Model\AttributeInterface'          => 'pim_catalog.entity.attribute.class',
            'Pim\Component\Catalog\Model\CompletenessInterface'    => 'pim_catalog.entity.completeness.class',
            'Pim\Component\Catalog\LocaleInterface'          => 'pim_catalog.entity.locale.class',
            'Pim\Bundle\CatalogBundle\Model\MetricInterface'          => 'pim_catalog.entity.metric.class',
            'Pim\Component\Catalog\Model\ProductInterface'            => 'pim_catalog.entity.product.class',
            'Pim\Bundle\CatalogBundle\Model\ProductMediaInterface'    => 'pim_catalog.entity.product_media.class',
            'Pim\Component\Catalog\Model\ProductPriceInterface'    => 'pim_catalog.entity.product_price.class',
            'Pim\Component\Catalog\Model\ProductValueInterface'       => 'pim_catalog.entity.product_value.class',
            'Pim\Component\Catalog\Model\CategoryInterface'        => 'pim_catalog.entity.category.class',
            'Pim\Bundle\CatalogBundle\Model\CurrencyInterface'        => 'pim_catalog.entity.currency.class',
            'Pim\Component\Catalog\Model\FamilyInterface'             => 'pim_catalog.entity.family.class',
            'Pim\Component\Catalog\ChannelInterface'         => 'pim_catalog.entity.channel.class',
            'Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface' => 'pim_catalog.entity.product_template.class',
        ];
    }
}
