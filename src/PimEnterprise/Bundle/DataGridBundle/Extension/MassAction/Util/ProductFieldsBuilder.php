<?php

namespace PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Util;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;
use Pim\Bundle\CatalogBundle\Manager\AssociationTypeManager;
use Pim\Bundle\TransformBundle\Normalizer\FlatProductNormalizer;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Util\ProductFieldsBuilder as PimProductFieldsBuilder;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AttributeGroupAccessRepository;
use PimEnterprise\Bundle\SecurityBundle\Voter\AttributeGroupVoter;

/**
 * Override to apply rights on attribute groups
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductFieldsBuilder extends PimProductFieldsBuilder
{
    /** @var AttributeGroupAccessRepository */
    protected $accessRepository;

    /** @var SecurityContextInterface */
    protected $securityContext;

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
     */
    public function __construct(
        ProductManager $productManager,
        LocaleManager $localeManager,
        CurrencyManager $currencyManager,
        AssociationTypeManager $assocTypeManager,
        CatalogContext $catalogContext,
        AttributeGroupAccessRepository $accessRepository,
        SecurityContextInterface $securityContext
    ) {
        parent::__construct($productManager, $localeManager, $currencyManager, $assocTypeManager, $catalogContext);
        $this->accessRepository = $accessRepository;
        $this->securityContext = $securityContext;

//        var_dump($this->securityContext->getUser());
//        die('eee');
    }

    /**
     * Override to filter only granted attributes
     */
    protected function prepareAvailableAttributeIds($productIds)
    {
        parent::prepareAvailableAttributeIds($productIds);

/*        $this->attributeIds = $this->accessRepository->filterGrantedAttributeIds(
            $this->securityContext->getUser(),
            AttributeGroupVoter::VIEW_ATTRIBUTES,
            $this->attributeIds
        );*/
    }
}
