<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Twig;

use Akeneo\UserManagement\Component\Model\UserInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Component\Security\Attributes;

/**
 * This twig extension provides several methods to know if user preferences are visible or not in the view mode
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class VisibleUserPreferencesExtension extends \Twig_Extension
{
    /** @var CategoryAccessRepository */
    protected $categoryAccessRepo;

    /**
     * @param CategoryAccessRepository $categoryAccessRepo
     */
    public function __construct(CategoryAccessRepository $categoryAccessRepo)
    {
        $this->categoryAccessRepo = $categoryAccessRepo;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'is_proposal_to_review_field_visible',
                [$this, 'isProposalToReviewFieldVisible']
            ),
            new \Twig_SimpleFunction(
                'is_proposal_state_field_visible',
                [$this, 'isProposalStateFieldVisible']
            ),
        ];
    }

    /**
     * Proposal to review field can be shown if the user owns at least one category.
     *
     * @param UserInterface $user
     *
     * @return bool
     */
    public function isProposalToReviewFieldVisible(UserInterface $user)
    {
        return $this->categoryAccessRepo->isOwner($user);
    }

    /**
     * Proposal state field can be shown if the user can edit at least to one category, but not if he owns
     * all categories he can edit.
     *
     * @param UserInterface $user
     *
     * @return bool
     */
    public function isProposalStateFieldVisible(UserInterface $user)
    {
        $editableCategories = $this->categoryAccessRepo->getGrantedCategoryCodes($user, Attributes::EDIT_ITEMS);
        $ownedCategories = $this->categoryAccessRepo->getGrantedCategoryCodes($user, Attributes::OWN_PRODUCTS);

        $editableButNotOwned = array_diff($editableCategories, $ownedCategories);

        return !empty($editableCategories) && !empty($editableButNotOwned);
    }
}
