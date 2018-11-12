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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Subscription;

/**
 * Holds the information needed to make a subscription to Franlin.
 *
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
final class Request
{
    /** @var array */
    private $identifiers;

    /** @var int */
    private $trackerId;

    /** @var array */
    private $familyInfos;

    /**
     * @param array $identifiers
     * @param int $trackerId
     * @param array $familyInfos
     */
    public function __construct(array $identifiers, int $trackerId, array $familyInfos)
    {
        $this->identifiers = $identifiers;
        $this->trackerId = $trackerId;
        $this->familyInfos = $familyInfos;
    }

    /**
     * @return array
     */
    public function identifiers(): array
    {
        return $this->identifiers;
    }

    /**
     * @return int
     */
    public function trackerId(): int
    {
        return $this->trackerId;
    }

    /**
     * @return array
     */
    public function familyInfos(): array
    {
        return $this->familyInfos;
    }
}
