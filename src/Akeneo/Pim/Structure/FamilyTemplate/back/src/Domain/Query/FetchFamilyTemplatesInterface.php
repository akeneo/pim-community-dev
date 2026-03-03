<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2023 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Structure\FamilyTemplate\Domain\Query;

use Akeneo\Pim\Structure\FamilyTemplate\Domain\ReadModel\FamilyTemplate;

interface FetchFamilyTemplatesInterface
{
    /**
     * @return array<FamilyTemplate>
     */
    public function all(): array;

    public function byName(string $templateName): FamilyTemplate;
}
