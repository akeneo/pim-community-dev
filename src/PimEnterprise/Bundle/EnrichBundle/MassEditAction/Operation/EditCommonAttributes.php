<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Manager\ProductMassActionManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\EditCommonAttributes as BaseEditCommonAttributes;
use Pim\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Edit common attributes of given products
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class EditCommonAttributes extends BaseEditCommonAttributes
{
    /** @var SecurityContextInterface */
    protected $securityContext;

    /**
     * Constructor
     *
     * @param ProductBuilder           $productBuilder
     * @param ProductUpdaterInterface  $productUpdater
     * @param UserContext              $userContext
     * @param CatalogContext           $catalogContext
     * @param ProductMassActionManager $massActionManager
     * @param NormalizerInterface      $normalizer
     * @param BulkSaverInterface       $productSaver
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(
        ProductBuilder $productBuilder,
        ProductUpdaterInterface $productUpdater,
        UserContext $userContext,
        CatalogContext $catalogContext,
        ProductMassActionManager $massActionManager,
        NormalizerInterface $normalizer,
        BulkSaverInterface $productSaver,
        SecurityContextInterface $securityContext
    ) {
        parent::__construct(
            $productBuilder,
            $productUpdater,
            $userContext,
            $catalogContext,
            $massActionManager,
            $normalizer,
            $productSaver
        );

        $this->securityContext = $securityContext;
    }

    /**
     * Get form options
     *
     * @return array
     */
    public function getFormOptions()
    {
        return array(
            'locales'           => $this->userContext->getGrantedUserLocales(Attributes::EDIT_PRODUCTS),
            'common_attributes' => $this->commonAttributes,
            'current_locale'    => $this->getLocale()->getCode()
        );
    }

    /**
     * {@inheritdoc}
     *
     * Prevent performing operation if current user does not own the product
     * Otherwise, product is directly updated and propostion is also created
     */
    protected function doPerform(ProductInterface $product)
    {
        if ($this->securityContext->isGranted(Attributes::OWN, $product)) {
            return parent::doPerform($product);
        }
    }
}
