<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\InternalApi;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetAllFamiliesLabelByLocaleQueryInterface
{
    public function execute(string $localeCode): array;
}
