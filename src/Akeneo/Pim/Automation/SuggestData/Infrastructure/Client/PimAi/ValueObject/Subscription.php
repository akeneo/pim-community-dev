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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject;

/**
 * Encapsulates a raw subscription from a raw API response returned by PIM.ai
 *
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class Subscription
{
    /** @var array */
    private $rawSubscription;

    /**
     * @param array $rawSubscription
     */
    public function __construct(array $rawSubscription)
    {
        $this->validateSubscription($rawSubscription);
        $this->rawSubscription = $rawSubscription;
    }

    /**
     * @return string
     */
    public function getSubscriptionId()
    {
        return $this->rawSubscription['id'];
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->rawSubscription['identifiers'] + $this->rawSubscription['attributes'];
    }

    /**
     * @return int
     */
    public function getTrackerId()
    {
        return $this->rawSubscription['tracker_id'];
    }

    /**
     * @param array $rawSubscription
     * @throws \InvalidArgumentException
     */
    private function validateSubscription(array $rawSubscription)
    {
        $expectedKeys = [
            'id',
            'identifiers',
            'attributes',
            'tracker_id'
        ];

        foreach ($expectedKeys as $key) {
            if (! array_key_exists($key, $rawSubscription)) {
                throw new \InvalidArgumentException(sprintf('Missing key "%s" in raw subscription data', $key));
            }
        }
    }
}
