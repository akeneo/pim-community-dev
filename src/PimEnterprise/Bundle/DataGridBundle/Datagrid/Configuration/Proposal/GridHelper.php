<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Proposal;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use PimEnterprise\Bundle\WorkflowBundle\Helper\ProductDraftChangesPermissionHelper;
use PimEnterprise\Bundle\WorkflowBundle\Provider\ProductDraftGrantedAttributeProvider;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Workflow\Model\ProductDraftInterface;
use PimEnterprise\Component\Workflow\Repository\ProductDraftRepositoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Helper for proposal datagrid
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class GridHelper
{
    /** @var ProductDraftRepositoryInterface $draftRepository */
    protected $draftRepository;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var RequestStack */
    protected $requestStack;

    /** @var ProductDraftGrantedAttributeProvider */
    protected $attributeProvider;

    /** @var ProductDraftChangesPermissionHelper */
    protected $permissionHelper;

    /**
     * @param ProductDraftRepositoryInterface      $draftRepository
     * @param AuthorizationCheckerInterface        $authorizationChecker
     * @param TokenStorageInterface                $tokenStorage
     * @param RequestStack                         $requestStack
     * @param ProductDraftGrantedAttributeProvider $attributeProvider
     * @param ProductDraftChangesPermissionHelper  $permissionHelper
     */
    public function __construct(
        ProductDraftRepositoryInterface $draftRepository,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        RequestStack $requestStack,
        ProductDraftGrantedAttributeProvider $attributeProvider,
        ProductDraftChangesPermissionHelper $permissionHelper
    ) {
        $this->draftRepository = $draftRepository;
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack;
        $this->attributeProvider = $attributeProvider;
        $this->permissionHelper = $permissionHelper;
    }

    /**
     * Returns callback that will disable approve and refuse buttons given permissions on proposal
     *
     * @return callable
     */
    public function getActionConfigurationClosure()
    {
        return function (ResultRecordInterface $record) {
            $canReview = $this->permissionHelper->canEditOneChangeToReview($record->getRootEntity());
            $toReview = $record->getRootEntity()->getStatus() === ProductDraftInterface::READY;
            $isOwner = $this->authorizationChecker->isGranted(Attributes::OWN, $record->getRootEntity()->getProduct());

            return [
                'approve' => $isOwner && $toReview && $canReview,
                'refuse'  => $isOwner && $toReview && $canReview
            ];
        };
    }

    /**
     * Returns available proposal author choices (author can be user or job instance)
     *
     * @return array
     */
    public function getAuthorChoices()
    {
        $authors = $this->draftRepository->getDistinctAuthors();
        $choices = array_combine($authors, $authors);

        return $choices;
    }

    /**
     * Returns available proposal product choices
     *
     * @return array
     */
    public function getProductChoices()
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $proposals = $this->draftRepository->findApprovableByUser($user);
        $choices = [];

        $choices = $this->generateLabel($proposals, $choices);

        asort($choices);

        return $choices;
    }

    /**
     * Returns available attribute choices for proposals
     *
     * @return array
     */
    public function getAttributeChoices()
    {
        $proposals = $this->draftRepository->findApprovableByUserAndProductId(
            $this->tokenStorage->getToken()->getUser(),
            $this->getProductContextFromRequest()
        );

        $attributes = [];
        foreach ($proposals as $proposal) {
            $attributes = array_merge($attributes, $this->attributeProvider->getViewable($proposal));
        }

        return $this->getGroupedAttributeChoices($attributes);
    }

    /**
     * Return choices indexed by unique group label like this: [
     *   'General (general)' => ['name' => 'Name', 'description' => 'Description']
     *   'General (general_bis)' => ['name' => 'Name']
     * ]
     *
     * @param AttributeInterface[] $attributes
     *
     * @return array
     */
    protected function getGroupedAttributeChoices(array $attributes)
    {
        $groups = $this->getGroupCodesByLabels($attributes);
        $choices = [];

        foreach ($attributes as $attribute) {
            $groupLabel = $attribute->getGroup()->getLabel();

            if (count($groups[$groupLabel]) > 1) {
                $groupLabel = sprintf('%s (%s)', $groupLabel, $attribute->getGroup()->getCode());
            }

            $choices[$groupLabel][$attribute->getCode()] = $attribute->getLabel();
        }

        return $choices;
    }

    /**
     * Return the group codes indexed by group label like this: ['General' => ['general', 'general_bis']]
     *
     * @param AttributeInterface[] $attributes
     *
     * @return array
     */
    protected function getGroupCodesByLabels(array $attributes)
    {
        $groups = [];

        foreach ($attributes as $attribute) {
            $group = $attribute->getGroup();

            if (!isset($groups[$group->getLabel()])) {
                $groups[$group->getLabel()] = [];
            }

            if (!in_array($group->getCode(), $groups[$group->getLabel()])) {
                $groups[$group->getLabel()][] = $group->getCode();
            }
        }

        return $groups;
    }

    /**
     * Retrieve the product identifier to filter the product drafts with from the request. This filter is used when
     * we display the product drafts of a product in the proposal tab.
     *
     * @return string|null
     */
    protected function getProductContextFromRequest()
    {
        if (!$this->requestStack->getCurrentRequest()->query->has('params')) {
            return null;
        }

        $params = $this->requestStack->getCurrentRequest()->query->get('params');

        if (!isset($params['product'])) {
            return null;
        }

        return $params['product'];
    }

    /**
     * It generates labels. If the proposal is a variant it concatenates with the axes values to prevent label
     * duplication.
     */
    private function generateLabel(array $proposals, array $choices): array
    {
        foreach ($proposals as $proposal) {
            $product = $proposal->getProduct();
            //TODO: be careful in 2.2 this interface will not distinguish variant from non variant.
            //It will need to be replaced by $product->isVariant().
            if ($product instanceof EntityWithFamilyVariantInterface) {
                $parentProduct = $product->getParent();
                if (null === $parentProduct) {
                    $choices[$product->getLabel()] = $product->getId();
                    continue;
                }

                $familyVariant = $parentProduct->getFamilyVariant();
                if (null === $familyVariant) {
                    $choices[$product->getLabel()] = $product->getId();
                    continue;
                }

                $variationLevel = $product->getVariationLevel();
                $variantAttributeSet = $familyVariant->getVariantAttributeSet($variationLevel);
                if (null === $variantAttributeSet) {
                    $choices[$product->getLabel()] = $product->getId();
                    continue;
                }

                $productLabel = trim($product->getLabel());
                $axes = $familyVariant->getAxes();

                foreach ($axes as $axe) {
                    $productLabel .= ' - '.$product->getValue($axe->getCode());
                }

                $choices[$productLabel] = $product->getId();
            } else {
                $choices[$product->getLabel()] = $product->getId();
            }
        }

        return $choices;
    }
}
