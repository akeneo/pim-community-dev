<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Util;

use PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;
use Pim\Bundle\CatalogBundle\Manager\AssociationTypeManager;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AttributeGroupAccessRepository;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class PublishedProductFieldsBuilder extends ProductFieldsBuilder
{
    /** @var PublishedProductManager */
    protected $publishedProductManager;

    /**
     * Constructor
     *
     * @param ProductManager                 $productManager
     * @param LocaleManager                  $localeManager
     * @param CurrencyManager                $currencyManager
     * @param AssociationTypeManager         $assocTypeManager
     * @param CatalogContext                 $catalogContext
     * @param AttributeGroupAccessRepository $accessRepository
     * @param SecurityContextInterface       $securityContext
     * @param PublishedProductManager        $publishedProductManager
     */
    public function __construct(
        ProductManager $productManager,
        LocaleManager $localeManager,
        CurrencyManager $currencyManager,
        AssociationTypeManager $assocTypeManager,
        CatalogContext $catalogContext,
        AttributeGroupAccessRepository $accessRepository,
        SecurityContextInterface $securityContext,
        PublishedProductManager $publishedProductManager
    ) {
        parent::__construct(
            $productManager,
            $localeManager,
            $currencyManager,
            $assocTypeManager,
            $catalogContext,
            $accessRepository,
            $securityContext
        );
        $this->publishedProductManager = $publishedProductManager;
    }

    /**
     * Override to filter only granted attributes
     *
     * {@inheritdoc}
     */
    protected function prepareAvailableAttributeIds($productIds)
    {
        $productRepo = $this->publishedProductManager->getProductRepository();
        $this->attributeIds = $productRepo->getAvailableAttributeIdsToExport($productIds);

        $this->attributeIds = $this
            ->accessRepository
            ->getGrantedAttributeIds(
                $this->securityContext->getToken()->getUser(),
                Attributes::VIEW_ATTRIBUTES,
                $this->attributeIds
            );
    }
}
