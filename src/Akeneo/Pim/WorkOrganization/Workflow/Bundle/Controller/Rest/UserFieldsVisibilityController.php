<?php

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Controller\Rest;

use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * This controller will send fields visibility for the user edit page.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class UserFieldsVisibilityController
{
    /** @var CategoryAccessRepository */
    protected $categoryAccessRepo;

    /** @var IdentifiableObjectRepositoryInterface */
    private $userRepository;

    /**
     * @param CategoryAccessRepository              $categoryAccessRepo
     * @param IdentifiableObjectRepositoryInterface $userRepository
     */
    public function __construct(
        CategoryAccessRepository $categoryAccessRepo,
        IdentifiableObjectRepositoryInterface $userRepository
    ) {
        $this->categoryAccessRepo = $categoryAccessRepo;
        $this->userRepository = $userRepository;
    }

    /**
     * @param Request $request
     * @param string  $identifier
     *
     * @return JsonResponse
     */
    public function getVisibilityAction(Request $request, string $identifier): JsonResponse
    {
        $user = $this->getUserOr404($identifier);

        return new JsonResponse([
            'proposals_to_review_notification' => $this->isProposalToReviewFieldVisible($user),
            'proposals_state_notifications'    => $this->isProposalStateFieldVisible($user)
        ]);
    }

    /**
     * Proposal to review field can be shown if the user owns at least one category.
     *
     * @param UserInterface $user
     *
     * @return bool
     */
    private function isProposalToReviewFieldVisible(UserInterface $user)
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
    private function isProposalStateFieldVisible(UserInterface $user)
    {
        $editableCategories = $this->categoryAccessRepo->getGrantedCategoryCodes($user, Attributes::EDIT_ITEMS);
        $ownedCategories = $this->categoryAccessRepo->getGrantedCategoryCodes($user, Attributes::OWN_PRODUCTS);

        $editableButNotOwned = array_diff($editableCategories, $ownedCategories);

        return !empty($editableCategories) && !empty($editableButNotOwned);
    }

    /**
     * @param  string $username
     *
     * @return UserInterface
     */
    private function getUserOr404($username): UserInterface
    {
        $user = $this->userRepository->findOneByIdentifier($username);

        if (null === $user) {
            throw new NotFoundHttpException(
                sprintf('Username with code "%s" not found', $username)
            );
        }

        return $user;
    }
}
