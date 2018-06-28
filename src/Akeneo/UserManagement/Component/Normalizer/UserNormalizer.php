<?php

namespace Akeneo\UserManagement\Component\Normalizer;

use Akeneo\UserManagement\Component\Model\UserInterface;
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
    /** @var array */
    protected $supportedFormats = ['array', 'standard', 'internal_api'];

    /**
     * {@inheritdoc}
     */
    public function normalize($user, $format = null, array $context = [])
    {
        return [
            'code'                   => $user->getUsername(), # Every Form Extension requires 'code' field.
            'username'               => $user->getUsername(),
            'email'                  => $user->getEmail(),
            'name_prefix'            => $user->getNamePrefix(),
            'first_name'             => $user->getFirstName(),
            'middle_name'            => $user->getMiddleName(),
            'last_name'              => $user->getLastName(),
            'name_suffix'            => $user->getNameSuffix(),
            'phone'                  => $user->getPhone(),
            'birthday'               => $user->getBirthday() ? $user->getBirthday()->getTimestamp() : null,
            'image'                  => $user->getImagePath(),
            'last_login'             => $user->getLastLogin() ? $user->getLastLogin()->getTimestamp() : null,
            'login_count'            => $user->getLoginCount(),
            'catalog_default_locale' => $user->getCatalogLocale()->getCode(),
            'user_default_locale'    => $user->getUiLocale()->getCode(),
            'catalog_default_scope'  => $user->getCatalogScope()->getCode(),
            'default_category_tree'  => $user->getDefaultTree()->getCode(),
            'avatar'                 => $user->getImagePath(),
            'timezone'               => $user->getTimezone(),
            'meta'                   => [
                'id'    => $user->getId(),
                'form'  => 'pim-user-edit-form',
                'image' => [
                    'filePath' => $user->getImagePath()
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
}
