<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Component;

final class Profiles
{
    /** I manage product catalogs. */
    const PRODUCT_MANAGER = 'product_manager';

    /** I enrich product data. */
    const REDACTOR = 'redactor';

    /** I integrate Akeneo PIM into our IT ecosystem.  */
    const PIM_INTEGRATOR = 'pim_integrator';

    /** I administrate Akeneo PIM. */
    const PIM_ADMINISTRATOR = 'pim_administrator';

    /** I manage assets in the PIM. */
    const ASSET_MANAGER = 'asset_manager';

    /** I translate product, asset, and/or reference entity data. */
    const TRANSLATOR = 'translator';

    /** I develop solutions connected with Akeneo PIM. */
    const THIRD_PARTY_DEVELOPER = 'third_party_developer';
}
