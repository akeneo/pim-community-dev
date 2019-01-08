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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class GetConnectionStatusQuery
{
    /** @var bool */
    private $checkTokenValidity;

    /**
     * @param bool $checkTokenValidity
     */
    public function __construct(bool $checkTokenValidity)
    {
        $this->checkTokenValidity = $checkTokenValidity;
    }

    /**
     * @return bool
     */
    public function checkTokenValidity(): bool
    {
        return $this->checkTokenValidity;
    }
}
