<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Domain\Query;

use Webmozart\Assert\Assert;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class Limit
{
    private int $limit;

    public function __construct(int $limit)
    {
        Assert::greaterThan($limit, 0, 'The limit should be greater than zero.');
        $this->limit = $limit;
    }

    public function intValue(): int
    {
        return $this->limit;
    }
}
