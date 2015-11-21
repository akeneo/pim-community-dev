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
use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;
use Pim\Bundle\CatalogBundle\Repository\AssociationTypeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
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
     * @param ProductRepositoryInterface         $productRepository
     * @param AttributeRepositoryInterface       $attributeRepository
     * @param LocaleRepositoryInterface          $localeRepository
     * @param CurrencyManager                    $currencyManager
     * @param AssociationTypeRepositoryInterface $assocTypeRepo
     * @param CatalogContext                     $catalogContext
     * @param AttributeGroupAccessRepository     $accessRepository
     * @param TokenStorageInterface              $tokenStorage
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        AttributeRepositoryInterface $attributeRepository,
        LocaleRepositoryInterface $localeRepository,
        CurrencyManager $currencyManager,
        AssociationTypeRepositoryInterface $assocTypeRepo,
        CatalogContext $catalogContext,
        AttributeGroupAccessRepository $accessRepository,
        TokenStorageInterface $tokenStorage
    ) {
        parent::__construct(
            $productRepository,
            $attributeRepository,
            $localeRepository,
            $currencyManager,
            $assocTypeRepo,
            $catalogContext
        );

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
