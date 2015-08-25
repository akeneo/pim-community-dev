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

use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Manager\AssociationTypeManager;
use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManagerInterface;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Util\ProductFieldsBuilder as BaseProductFieldsBuilder;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AttributeGroupAccessRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Override to apply permissions on attribute groups
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class ProductFieldsBuilder extends BaseProductFieldsBuilder
{
    /** @var AttributeGroupAccessRepository */
    protected $accessRepository;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /**
     * Constructor
     *
     * @param ProductManagerInterface        $productManager
     * @param LocaleManager                  $localeManager
     * @param CurrencyManager                $currencyManager
     * @param AssociationTypeManager         $assocTypeManager
     * @param CatalogContext                 $catalogContext
     * @param AttributeGroupAccessRepository $accessRepository
     * @param TokenStorageInterface          $tokenStorage
     */
    public function __construct(
        ProductManagerInterface $productManager,
        LocaleManager $localeManager,
        CurrencyManager $currencyManager,
        AssociationTypeManager $assocTypeManager,
        CatalogContext $catalogContext,
        AttributeGroupAccessRepository $accessRepository,
        TokenStorageInterface $tokenStorage
    ) {
        parent::__construct($productManager, $localeManager, $currencyManager, $assocTypeManager, $catalogContext);

        $this->accessRepository = $accessRepository;
        $this->tokenStorage     = $tokenStorage;
    }

    /**
     * Override to filter only granted attributes
     *
     * {@inheritdoc}
     */
    protected function prepareAvailableAttributeIds($productIds)
    {
        parent::prepareAvailableAttributeIds($productIds);

        if (empty($this->attributeIds)) {
            return;
        }

        $this->attributeIds = $this
            ->accessRepository
            ->getGrantedAttributeIds(
                $this->tokenStorage->getToken()->getUser(),
                Attributes::VIEW_ATTRIBUTES,
                $this->attributeIds
            );
    }
}
