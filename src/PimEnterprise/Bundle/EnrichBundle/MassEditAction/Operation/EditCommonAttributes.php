<?php

namespace PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation;

use Pim\Bundle\EnrichBundle\MassEditAction\Operation\EditCommonAttributes as BaseEditCommonAttributes;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Manager\ProductMassActionManager;
use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class EditCommonAttributes extends BaseEditCommonAttributes
{
    /** @var SecurityContextInterface */
    protected $securityContext;

    /**
     * @param ProductManager           $productManager
     * @param UserContext              $userContext
     * @param CurrencyManager          $currencyManager
     * @param CatalogContext           $catalogContext
     * @param ProductBuilder           $productBuilder
     * @param ProductMassActionManager $massActionManager
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(
        ProductManager $productManager,
        UserContext $userContext,
        CurrencyManager $currencyManager,
        CatalogContext $catalogContext,
        ProductBuilder $productBuilder,
        ProductMassActionManager $massActionManager,
        SecurityContextInterface $securityContext
    ) {
        parent::__construct(
            $productManager,
            $userContext,
            $currencyManager,
            $catalogContext,
            $productBuilder,
            $massActionManager
        );
        $this->securityContext = $securityContext;
    }

    /**
     * {@inheritdoc}
     */
    protected function doPerform(ProductInterface $product)
    {
        if (!$this->securityContext->isGranted(Attributes::OWNER, $product)) {
            return;
        }

        return parent::doPerform($product);
    }
}
