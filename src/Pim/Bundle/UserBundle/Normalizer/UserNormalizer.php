<?php

namespace Pim\Bundle\UserBundle\Normalizer;

use Pim\Bundle\UserBundle\Entity\UserInterface;
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
    protected $supportedFormats = ['array', 'json', 'internal_api'];

    /**
     * {@inheritdoc}
     */
    public function normalize($user, $format = null, array $context = [])
    {
        return [
            'username'      => $user->getUsername(),
            'email'         => $user->getEmail(),
            'namePrefix'    => $user->getNamePrefix(),
            'firstName'     => $user->getFirstName(),
            'middleName'    => $user->getMiddleName(),
            'lastName'      => $user->getLastName(),
            'nameSuffix'    => $user->getNameSuffix(),
            'birthday'      => $user->getBirthday() ? $user->getBirthday()->getTimestamp() : null,
            'image'         => $user->getImagePath(),
            'lastLogin'     => $user->getLastLogin() ? $user->getLastLogin()->getTimestamp() : null,
            'loginCount'    => $user->getLoginCount(),
            'catalogLocale' => $user->getCatalogLocale()->getCode(),
            'uiLocale'      => $user->getUiLocale()->getCode(),
            'catalogScope'  => $user->getCatalogScope()->getCode(),
            'defaultTree'   => $user->getDefaultTree()->getCode(),
            'meta'          => [
                'id' => $user->getId()
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
