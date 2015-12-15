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

use Akeneo\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Manager\ProductMassActionManager;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\EditCommonAttributes as BaseEditCommonAttributes;
use Pim\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Edit common attributes of given products
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class EditCommonAttributes extends BaseEditCommonAttributes
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * Constructor
     *
     * @param ProductBuilder                $productBuilder
     * @param UserContext                   $userContext
     * @param CatalogContext                $catalogContext
     * @param AttributeRepositoryInterface  $attributeRepository
     * @param NormalizerInterface           $normalizer
     * @param FileStorerInterface           $fileStorer
     * @param ProductMassActionManager      $massActionManager
     * @param ObjectUpdaterInterface        $productUpdater
     * @param ValidatorInterface            $productValidator
     * @param NormalizerInterface           $internalNormalizer
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param string                        $tmpStorageDir
     */
    public function __construct(
        ProductBuilder $productBuilder,
        UserContext $userContext,
        CatalogContext $catalogContext,
        AttributeRepositoryInterface $attributeRepository,
        NormalizerInterface $normalizer,
        FileStorerInterface $fileStorer,
        ProductMassActionManager $massActionManager,
        ObjectUpdaterInterface $productUpdater,
        ValidatorInterface $productValidator,
        NormalizerInterface $internalNormalizer,
        AuthorizationCheckerInterface $authorizationChecker,
        $tmpStorageDir = null
    ) {
        parent::__construct(
            $productBuilder,
            $userContext,
            $catalogContext,
            $attributeRepository,
            $normalizer,
            $fileStorer,
            $massActionManager,
            $productUpdater,
            $productValidator,
            $internalNormalizer,
            $tmpStorageDir
        );

        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated Will be removed in 1.6
     *
     * We override parent to keep only attributes the user can edit
     */
    public function getAllAttributes()
    {
        $allAttributes = parent::getAllAttributes();
        $grantedAttributes = [];

        foreach ($allAttributes as $attribute) {
            $canEditAttribute = $this->authorizationChecker->isGranted(
                Attributes::EDIT_ATTRIBUTES,
                $attribute->getGroup()
            );

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
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getBatchJobCode()
    {
        return 'edit_common_attributes_with_permission';
    }
}
