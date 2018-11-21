<?php

namespace Akeneo\UserManagement\Component\Normalizer;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\PimDataGridBundle\Repository\DatagridViewRepositoryInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * User normalizer
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserNormalizer implements NormalizerInterface
{
    /** @var DateTimeNormalizer */
    private $dateTimeNormalizer;

    /** @var NormalizerInterface */
    private $fileNormalizer;

    /** @var SecurityFacade */
    private $securityFacade;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var DatagridViewRepositoryInterface */
    private $datagridViewRepo;

    /** @var IdentifiableObjectRepositoryInterface|null */
    private $categoryAccessRepository;

    /** @var array */
    protected $supportedFormats = ['array', 'standard', 'internal_api'];

    /**
     * @param DateTimeNormalizer $dateTimeNormalizer
     * @param NormalizerInterface $fileNormalizer
     * @param SecurityFacade $securityFacade
     * @param TokenStorageInterface $tokenStorage
     * @param DatagridViewRepositoryInterface $datagridViewRepo
     * @param IdentifiableObjectRepositoryInterface|null $categoryAccessRepository
     */
    public function __construct(
        DateTimeNormalizer $dateTimeNormalizer,
        NormalizerInterface $fileNormalizer,
        SecurityFacade $securityFacade,
        TokenStorageInterface $tokenStorage,
        DatagridViewRepositoryInterface $datagridViewRepo,
        ?IdentifiableObjectRepositoryInterface $categoryAccessRepository = null
    ) {
        $this->dateTimeNormalizer = $dateTimeNormalizer;
        $this->fileNormalizer = $fileNormalizer;
        $this->securityFacade = $securityFacade;
        $this->tokenStorage = $tokenStorage;
        $this->datagridViewRepo = $datagridViewRepo;
        $this->categoryAccessRepository = $categoryAccessRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($user, $format = null, array $context = [])
    {
        $result = [
            'code'                      => $user->getUsername(), # Every Form Extension requires 'code' field.
            'enabled'                   => $user->isEnabled(),
            'username'                  => $user->getUsername(),
            'email'                     => $user->getEmail(),
            'name_prefix'               => $user->getNamePrefix(),
            'first_name'                => $user->getFirstName(),
            'middle_name'               => $user->getMiddleName(),
            'last_name'                 => $user->getLastName(),
            'name_suffix'               => $user->getNameSuffix(),
            'phone'                     => $user->getPhone(),
            'birthday'                  => $user->getBirthday() ? $user->getBirthday()->format('Y-m-d') : null,
            'image'                     => $user->getImagePath(),
            'last_login'                => $user->getLastLogin() ? $user->getLastLogin()->getTimestamp() : null,
            'login_count'               => $user->getLoginCount(),
            'catalog_default_locale'    => $user->getCatalogLocale()->getCode(),
            'user_default_locale'       => $user->getUiLocale()->getCode(),
            'catalog_default_scope'     => $user->getCatalogScope()->getCode(),
            'default_category_tree'     => $user->getDefaultTree()->getCode(),
            'email_notifications'       => $user->isEmailNotifications(),
            'asset_delay_reminder'      => $user->getAssetDelayReminder(),
            'default_asset_tree'        => $user->getDefaultAssetTree() ? $user->getDefaultAssetTree()->getCode() : null,
            'display_proposals_to_review_notification' => $this->displayProposalsToReviewNotification($user),
            'proposals_to_review_notification' => $user->hasProposalsToReviewNotification(),
            'display_proposals_state_notifications' => $this->displayProposalsStateNotification($user),
            'proposals_state_notifications' => $user->hasProposalsStateNotification(),
            'timezone'                  => $user->getTimezone(),
            'groups'                    => $user->getGroupNames(),
            'roles'                     => $this->getRoleNames($user),
            'product_grid_filters'      => $user->getProductGridFilters(),
            'avatar'                    => null === $user->getAvatar() ? [
                'filePath'         => null,
                'originalFilename' => null,
            ] : $this->fileNormalizer->normalize($user->getAvatar()),
            'meta'                      => [
                'id'    => $user->getId(),
                'form'  => $this->isEditGranted($user) ? 'pim-user-edit-form' : 'pim-user-show',
                'image' => [
                    'filePath' => null === $user->getAvatar() ?
                        null :
                        $this->fileNormalizer->normalize($user->getAvatar())['filePath']
                ]
            ]
        ];

        $types = $this->datagridViewRepo->getDatagridViewTypeByUser($user);
        foreach ($types as $type) {
            $defaultView = $user->getDefaultGridView($type['datagridAlias']);
            // Set default_product_grid_view, default_published_product_grid_view, etc.
            $result[sprintf('default_%s_view', str_replace('-', '_', $type['datagridAlias']))]
                = $defaultView === null ? null : $defaultView->getId();
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof UserInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * @param UserInterface $user
     *
     * @return string[]
     */
    private function getRoleNames(UserInterface $user): array
    {
        return $user->getRolesCollection()->map(function (Role $role) {
            return $role->getRole();
        })->toArray();
    }

    /**
     * Returns true if the current user has permission to edit user or is the current user.
     *
     * @param UserInterface $user
     *
     * @return bool
     */
    private function isEditGranted($user): bool
    {
        if ($this->securityFacade->isGranted('pim_user_user_edit')) {
            return true;
        }

        $token = $this->tokenStorage->getToken();
        $currentUser = $token ? $token->getUser() : null;

        return ($user->getId() && is_object($currentUser) && $currentUser->getId() == $user->getId());
    }

    private function displayProposalsToReviewNotification($user): bool
    {
        if ($this->categoryAccessRepository === null) {
            return false;
        }

        return $this->categoryAccessRepository->isOwner($user);
    }

    private function displayProposalsStateNotification($user): bool
    {
        if ($this->categoryAccessRepository === null) {
            return false;
        }

        $editableCategories = $this->categoryAccessRepository
            ->getGrantedCategoryCodes($user, constant('Akeneo\Pim\Permission\Component\Attributes::EDIT_ITEMS'));
        $ownedCategories = $this->categoryAccessRepository
            ->getGrantedCategoryCodes($user, constant('Akeneo\Pim\Permission\Component\Attributes::OWN_PRODUCTS'));

        $editableButNotOwned = array_diff($editableCategories, $ownedCategories);

        return !empty($editableCategories) && !empty($editableButNotOwned);
    }
}
