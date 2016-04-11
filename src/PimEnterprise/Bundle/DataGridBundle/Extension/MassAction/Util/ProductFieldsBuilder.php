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
use Pim\Bundle\DataGridBundle\Extension\MassAction\Util\ProductFieldsBuilder as BaseProductFieldsBuilder;
use Pim\Component\Catalog\Repository\AssociationTypeRepositoryInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\CurrencyRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AttributeGroupAccessRepository;
use PimEnterprise\Component\Security\Attributes;
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
     * @param CurrencyRepositoryInterface        $currencyRepository
     * @param AssociationTypeRepositoryInterface $assocTypeRepo
     * @param CatalogContext                     $catalogContext
     * @param AttributeGroupAccessRepository     $accessRepository
     * @param TokenStorageInterface              $tokenStorage
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        AttributeRepositoryInterface $attributeRepository,
        LocaleRepositoryInterface $localeRepository,
        CurrencyRepositoryInterface $currencyRepository,
        AssociationTypeRepositoryInterface $assocTypeRepo,
        CatalogContext $catalogContext,
        AttributeGroupAccessRepository $accessRepository,
        TokenStorageInterface $tokenStorage
    ) {
        parent::__construct(
            $productRepository,
            $attributeRepository,
            $localeRepository,
            $currencyRepository,
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
