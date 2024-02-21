<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Bundle\Storage;

use Akeneo\UserManagement\Component\Profiles;
use Akeneo\UserManagement\Component\Storage\ProfileRepositoryInterface;

class ProfileRepository implements ProfileRepositoryInterface
{
    private const TRANSLATION_PREFIX = 'pim_user.profile.';

    public function findAll(): array
    {
        return [
            [
                'code' => Profiles::PRODUCT_MANAGER,
                'label' => self::TRANSLATION_PREFIX . Profiles::PRODUCT_MANAGER,
            ],
            [
                'code' => Profiles::REDACTOR,
                'label' => self::TRANSLATION_PREFIX . Profiles::REDACTOR,
            ],
            [
                'code' => Profiles::PIM_INTEGRATOR,
                'label' => self::TRANSLATION_PREFIX . Profiles::PIM_INTEGRATOR,
            ],
            [
                'code' => Profiles::PIM_ADMINISTRATOR,
                'label' => self::TRANSLATION_PREFIX . Profiles::PIM_ADMINISTRATOR,
            ],
            [
                'code' => Profiles::ASSET_MANAGER,
                'label' => self::TRANSLATION_PREFIX . Profiles::ASSET_MANAGER,
            ],
            [
                'code' => Profiles::TRANSLATOR,
                'label' => self::TRANSLATION_PREFIX . Profiles::TRANSLATOR,
            ],
            [
                'code' => Profiles::THIRD_PARTY_DEVELOPER,
                'label' => self::TRANSLATION_PREFIX . Profiles::THIRD_PARTY_DEVELOPER,
            ],
        ];
    }
}
