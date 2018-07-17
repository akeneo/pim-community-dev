<?php

namespace Akeneo\UserManagement\Component\Normalizer;

use Akeneo\UserManagement\Component\Model\UserInterface;
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

    /** @var array */
    protected $supportedFormats = ['array', 'standard', 'internal_api'];

    public function __construct(
        DateTimeNormalizer $dateTimeNormalizer,
        NormalizerInterface $fileNormalizer,
        SecurityFacade $securityFacade,
        TokenStorageInterface $tokenStorage
    ) {
        $this->dateTimeNormalizer = $dateTimeNormalizer;
        $this->fileNormalizer = $fileNormalizer;
        $this->securityFacade = $securityFacade;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($user, $format = null, array $context = [])
    {
        /** @var UserInterface $user */
        return [
            'code'                   => $user->getUsername(), # Every Form Extension requires 'code' field.
            'enabled'                => $user->isEnabled(),
            'username'               => $user->getUsername(),
            'email'                  => $user->getEmail(),
            'name_prefix'            => $user->getNamePrefix(),
            'first_name'             => $user->getFirstName(),
            'middle_name'            => $user->getMiddleName(),
            'last_name'              => $user->getLastName(),
            'name_suffix'            => $user->getNameSuffix(),
            'phone'                  => $user->getPhone(),
            'birthday'               => $user->getBirthday() ? $user->getBirthday()->format('Y-m-d') : null,
            'image'                  => $user->getImagePath(),
            'last_login'             => $user->getLastLogin() ? $user->getLastLogin()->getTimestamp() : null,
            'login_count'            => $user->getLoginCount(),
            'catalog_default_locale' => $user->getCatalogLocale()->getCode(),
            'user_default_locale'    => $user->getUiLocale()->getCode(),
            'catalog_default_scope'  => $user->getCatalogScope()->getCode(),
            'default_category_tree'  => $user->getDefaultTree()->getCode(),
            'timezone'               => $user->getTimezone(),
            'groups'                 => $user->getGroupNames(),
            'roles'                  => $this->getRoleNames($user),
            'avatar'                 => null === $user->getAvatar() ? [
                'filePath'         => null,
                'originalFilename' => null,
            ] : $this->fileNormalizer->normalize($user->getAvatar()),
            'meta'                   => [
                'id'    => $user->getId(),
                'form'  => $this->isEditGranted($user) ? 'pim-user-edit-form' : 'pim-user-show',
                'image' => [
                    'filePath' => null === $user->getAvatar() ?
                        null :
                        $this->fileNormalizer->normalize($user->getAvatar())['filePath']
                ]
            ]
        ];
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
        $roles = $user->getRolesCollection();
        $result = [];
        foreach ($roles as $role) {
            $result[] = $role->getRole();
        }

        return $result;
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
}
