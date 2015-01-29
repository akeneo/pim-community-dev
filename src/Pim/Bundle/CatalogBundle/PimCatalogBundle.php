<?php

namespace Pim\Bundle\CatalogBundle;

use Akeneo\Bundle\StorageUtilsBundle\AkeneoStorageUtilsBundle;
use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\ResolveDoctrineTargetRepositoryPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterAttributeConstraintGuessersPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterAttributeTypePass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterProductQueryFilterPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterProductQuerySorterPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterProductUpdaterPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterQueryGeneratorsPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\ResolveDoctrineTargetModelPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Pim Catalog Bundle
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimCatalogBundle extends AkeneoStorageUtilsBundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new ResolveDoctrineTargetRepositoryPass('pim_repository'))
            ->addCompilerPass(new RegisterAttributeConstraintGuessersPass())
            ->addCompilerPass(new RegisterAttributeTypePass())
            ->addCompilerPass(new RegisterQueryGeneratorsPass())
            ->addCompilerPass(new RegisterProductQueryFilterPass())
            ->addCompilerPass(new RegisterProductQuerySorterPass())
            ->addCompilerPass(new RegisterProductUpdaterPass());

        parent::build($container);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDoctrineMappingDriverConfig()
    {
        return [$this->getDoctrineMappingDriverDirectory('model/doctrine') => 'Pim\Bundle\CatalogBundle\Model'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getModelInterfaces()
    {
        return array(
            'Symfony\Component\Security\Core\User\UserInterface'   => 'oro_user.entity.class',
            'Pim\Bundle\CatalogBundle\Model\AssociationInterface'  => 'pim_catalog.entity.association.class',
            'Pim\Bundle\CatalogBundle\Model\AttributeInterface'    => 'pim_catalog.entity.attribute.class',
            'Pim\Bundle\CatalogBundle\Model\CompletenessInterface' => 'pim_catalog.entity.completeness.class',
            'Pim\Bundle\CatalogBundle\Model\LocaleInterface'       => 'pim_catalog.entity.locale.class',
            'Pim\Bundle\CatalogBundle\Model\MetricInterface'       => 'pim_catalog.entity.metric.class',
            'Pim\Bundle\CatalogBundle\Model\ProductInterface'      => 'pim_catalog.entity.product.class',
            'Pim\Bundle\CatalogBundle\Model\ProductMediaInterface' => 'pim_catalog.entity.product_media.class',
            'Pim\Bundle\CatalogBundle\Model\ProductPriceInterface' => 'pim_catalog.entity.product_price.class',
            'Pim\Bundle\CatalogBundle\Model\ProductValueInterface' => 'pim_catalog.entity.product_value.class',
            'Pim\Bundle\CatalogBundle\Model\CategoryInterface'     => 'pim_catalog.entity.category.class',
            'Pim\Bundle\CatalogBundle\Model\CurrencyInterface'     => 'pim_catalog.entity.currency.class',
            'Pim\Bundle\CatalogBundle\Model\FamilyInterface'       => 'pim_catalog.entity.family.class',
            'Pim\Bundle\CatalogBundle\Model\ChannelInterface'      => 'pim_catalog.entity.channel.class',
        );
    }
}
