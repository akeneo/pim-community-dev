<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Component\Query;

/**
 * Get raw values of a product / product model. Values of the entity are merged with values of its parent(s).
 */
interface GetRawValues
{
    /**
     * @param string|int $id
     */
    public function forProductId($id): ?array;

    public function forProductModelId(int $id): ?array;
}
