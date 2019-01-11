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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\Subscription;

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
     * @return array
     */
    public function toFormParams(): array
    {
        $params = [];
        foreach ($this->requests as $request) {
            $params[] = $this->formatIdentifiers($request->identifiers()) + [
                'tracker_id' => $request->trackerId(),
                'family' => $request->familyInfos(),
            ];
        }

        return $params;
    }

    /**
     * @param array $identifiers
     *
     * @return array
     */
    private function formatIdentifiers(array $identifiers): array
    {
        $formatted = [];
        if (isset($identifiers['asin'])) {
            $formatted['asin'] = $identifiers['asin'];
        }
        if (isset($identifiers['upc'])) {
            $formatted['upc'] = $identifiers['upc'];
        }
        if (isset($identifiers['mpn']) && isset($identifiers['brand'])) {
            $formatted['mpn_brand'] = [
                'mpn' => $identifiers['mpn'],
                'brand' => $identifiers['brand'],
            ];
        }

        return $formatted;
    }
}
