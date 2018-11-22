<?php

declare(strict_types=1);

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\InternalApi;

use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserNormalizer implements NormalizerInterface
{
    /** @var CategoryAccessRepository */
    private $categoryAccessRepository;

    /**
     * @param CategoryAccessRepository $categoryAccessRepository
     */
    public function __construct(CategoryAccessRepository $categoryAccessRepository)
    {
        $this->categoryAccessRepository = $categoryAccessRepository;
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

    private function displayProposalsToReviewNotification($user): bool
    {
        return $this->categoryAccessRepository->isOwner($user);
    }

    private function displayProposalsStateNotification($user): bool
    {
        $editableCategories = $this->categoryAccessRepository
            ->getGrantedCategoryCodes($user, Attributes::EDIT_ITEMS);
        $ownedCategories = $this->categoryAccessRepository
            ->getGrantedCategoryCodes($user, Attributes::OWN_PRODUCTS);

        $editableButNotOwned = array_diff($editableCategories, $ownedCategories);

        return !empty($editableCategories) && !empty($editableButNotOwned);
    }
}
