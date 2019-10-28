<?php

declare(strict_types=1);

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\InternalApi;

use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category\GetGrantedCategoryCodes;
use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var CategoryAccessRepository */
    private $categoryAccessRepository;

    /** @var GetGrantedCategoryCodes */
    private $getAllEditableCategoryCodes;

    /** @var GetGrantedCategoryCodes */
    private $getAllOwnableCategoryCodes;

    public function __construct(
        CategoryAccessRepository $categoryAccessRepository,
        GetGrantedCategoryCodes $getAllEditableCategoryCodes,
        GetGrantedCategoryCodes $getAllOwnableCategoryCodes
    ) {
        $this->categoryAccessRepository = $categoryAccessRepository;
        $this->getAllEditableCategoryCodes = $getAllEditableCategoryCodes;
        $this->getAllOwnableCategoryCodes = $getAllOwnableCategoryCodes;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($user, $format = null, array $context = array()): array
    {
        return [
            'display_proposals_to_review_notification' => $this->displayProposalsToReviewNotification($user),
            'display_proposals_state_notifications' => $this->displayProposalsStateNotification($user),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof UserInterface && 'internal_api' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    private function displayProposalsToReviewNotification(UserInterface $user): bool
    {
        return $this->categoryAccessRepository->isOwner($user);
    }

    private function displayProposalsStateNotification(UserInterface $user): bool
    {
        $userGroupIds = $user->getGroupsIds();
        $editableCategories = $this->getAllEditableCategoryCodes->forGroupIds($userGroupIds);
        $ownedCategories = $this->getAllOwnableCategoryCodes->forGroupIds($userGroupIds);

        $editableButNotOwned = array_diff($editableCategories, $ownedCategories);

        return !empty($editableCategories) && !empty($editableButNotOwned);
    }
}
