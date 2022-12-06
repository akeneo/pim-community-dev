<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso\User;

use Hslavich\OneloginSamlBundle\Security\User\SamlUserFactoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Creates a PIM user on-the-fly from a SAML token if the provisioning is enabled, throws an exception otherwise.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
final class Factory implements SamlUserFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createUser($username, array $attributes = []): UserInterface
    {
        // TODO AOB-340: Create the user if the provisioning is enabled, else:
        throw new UnknownUserException(
            $username,
            sprintf('The user provisioning is disabled and the user "%s" does not exist.', $username)
        );
    }
}
