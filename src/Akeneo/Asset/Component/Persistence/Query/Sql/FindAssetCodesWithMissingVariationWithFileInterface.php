<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Component\Persistence\Query\Sql;

/**
 * Find the codes of the assets that have at least one missing variation that should have a file.
 */
interface FindAssetCodesWithMissingVariationWithFileInterface
{
    /**
     * @return string[] Asset codes
     */
    public function execute(): array;
}
