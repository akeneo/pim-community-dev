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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Subscription\Write;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
final class RequestCollection
{
    /** @var Request[] */
    private $requests = [];

    /**
     * @param Request $request
     */
    public function add(Request $request): void
    {
        $this->requests[] = $request;
    }

    /**
     * @param int $index
     *
     * @return Request|null
     */
    public function get(int $index): ?Request
    {
        return $this->requests[$index] ?? null;
    }

    /**
     * @return array
     */
    public function toFormParams(): array
    {
        $params = [];
        foreach ($this->requests as $request) {
            $params[] = $request->identifiers() + [
                'tracker_id' => $request->trackerId(),
                'family' => $request->familyInfos(),
            ];
        }

        return $params;
    }
}
