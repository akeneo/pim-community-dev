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
 * Represents a warning issued by Franklin when trying to subscribe a product.
 *
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
final class Warning
{
    /** @var array */
    private $rawWarning;

    /**
     * @param array $rawWarning
     */
    public function __construct(array $rawWarning)
    {
        $this->validate($rawWarning);
        $this->rawWarning = $rawWarning;
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return $this->rawWarning['message'];
    }

    /**
     * @return int
     */
    public function trackerId(): int
    {
        return (int) $this->rawWarning['entry']['tracker_id'];
    }

    /**
     * @param array $rawWarning
     */
    private function validate(array $rawWarning): void
    {
        $expectedKeys = [
            'message',
            'entry',
        ];

        foreach ($expectedKeys as $key) {
            if (!array_key_exists($key, $rawWarning)) {
                throw new \InvalidArgumentException(sprintf('Missing key "%s" in raw warning data', $key));
            }
        }

        if (!isset($rawWarning['entry']['tracker_id'])) {
            throw new \InvalidArgumentException('Missing "tracker_id" in raw warning data');
        }
    }
}
