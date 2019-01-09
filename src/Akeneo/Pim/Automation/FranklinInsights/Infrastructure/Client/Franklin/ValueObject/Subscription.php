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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject;

/**
 * Encapsulates a raw subscription from a raw API response returned by Franklin.
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
    public function getSubscriptionId(): string
    {
        return $this->rawSubscription['id'];
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return array_merge($this->rawSubscription['mapped_identifiers'], $this->rawSubscription['mapped_attributes']);
    }

    /**
     * @return int
     */
    public function getTrackerId(): int
    {
        return (int) $this->rawSubscription['extra']['tracker_id'];
    }

    /**
     * @return bool
     */
    public function isMappingMissing(): bool
    {
        return true === $this->rawSubscription['misses_mapping'];
    }

    /**
     * @return bool
     */
    public function isCancelled(): bool
    {
        return array_key_exists('is_cancelled', $this->rawSubscription) ?
            $this->rawSubscription['is_cancelled'] :
            false;
    }

    /**
     * @param array $rawSubscription
     *
     * @throws \InvalidArgumentException
     */
    private function validateSubscription(array $rawSubscription): void
    {
        $expectedKeys = [
            'id',
            'mapped_identifiers',
            'mapped_attributes',
            'extra',
            'misses_mapping',
        ];

        foreach ($expectedKeys as $key) {
            if (!array_key_exists($key, $rawSubscription)) {
                throw new \InvalidArgumentException(sprintf('Missing key "%s" in raw subscription data', $key));
            }
        }

        if (!isset($rawSubscription['extra']['tracker_id'])) {
            throw new \InvalidArgumentException('Missing "tracker_id" in raw subscription data');
        }

        foreach (['mapped_identifiers', 'mapped_attributes'] as $key) {
            if (!is_array($rawSubscription[$key])) {
                throw new \InvalidArgumentException(
                    sprintf('key "%s" must be an array in raw subscription data', $key)
                );
            }
            foreach ($rawSubscription[$key] as $index => $value) {
                if (!isset($value['name']) || !array_key_exists('value', $value)) {
                    throw new \InvalidArgumentException(
                        sprintf('Missing key "name" or "value" for "%s"[%d] in raw suggested data', $key, $index)
                    );
                }
            }
        }
    }
}
