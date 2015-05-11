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

use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Manager\ProductMassActionManager;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
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
     * @param ProductBuilder               $productBuilder
     * @param UserContext                  $userContext
     * @param CatalogContext               $catalogContext
     * @param AttributeRepositoryInterface $attributeRepository
     * @param NormalizerInterface          $normalizer
     * @param MediaManager                 $mediaManager
     * @param ProductMassActionManager     $massActionManager
     * @param string                       $uploadDir
     * @param SecurityContextInterface     $securityContext
     */
    public function __construct(
        ProductBuilder $productBuilder,
        UserContext $userContext,
        CatalogContext $catalogContext,
        AttributeRepositoryInterface $attributeRepository,
        NormalizerInterface $normalizer,
        MediaManager $mediaManager,
        ProductMassActionManager $massActionManager,
        $uploadDir,
        SecurityContextInterface $securityContext
    ) {
        parent::__construct(
            $productBuilder,
            $userContext,
            $catalogContext,
            $attributeRepository,
            $normalizer,
            $mediaManager,
            $massActionManager,
            $uploadDir
        );

        $this->securityContext = $securityContext;
    }

    /**
     * {@inheritdoc}
     *
     * We override parent to keep only attributes the user can edit
     */
    public function getAllAttributes()
    {
        $allAttributes = parent::getAllAttributes();
        $grantedAttributes = [];

        foreach ($allAttributes as $attribute) {
            $canEditAttribute = $this->securityContext->isGranted(Attributes::EDIT_ATTRIBUTES, $attribute->getGroup());

            if ($canEditAttribute) {
                $grantedAttributes[] = $attribute;
            }
        }

        return $grantedAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormOptions()
    {
        return [
            'locales'        => $this->userContext->getGrantedUserLocales(Attributes::EDIT_PRODUCTS),
            'all_attributes' => $this->getAllAttributes(),
            'current_locale' => $this->getLocale()->getCode()
        ];
    }
}
