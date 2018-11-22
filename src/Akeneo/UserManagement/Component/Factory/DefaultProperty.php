<?php
declare(strict_types=1);

namespace Akeneo\UserManagement\Component\Factory;

use Akeneo\UserManagement\Component\Model\UserInterface;

/**
 * Interface to mutate User's default property
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface DefaultProperty
{
    /**
     * @param UserInterface $user
     *
     * @return UserInterface
     */
    public function mutate(UserInterface $user): UserInterface;
}
