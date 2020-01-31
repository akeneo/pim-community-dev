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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Model\Write;

final class AsyncRequest
{
    /** @var array */
    private $data;

    /** @var callable */
    private $onFulfilled;

    /** @var callable */
    private $onRejected;

    public function __construct(array $data, callable $onFulfilled, callable $onRejected)
    {
        $this->data = $data;
        $this->onFulfilled = $onFulfilled;
        $this->onRejected = $onRejected;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getOnFulfilled(): callable
    {
        return $this->onFulfilled;
    }

    public function getOnRejected(): callable
    {
        return $this->onRejected;
    }
}
