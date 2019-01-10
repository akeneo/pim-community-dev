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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Time;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class RealDateTimeBuilder implements DateTimeBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildForFirstProductFetch(): \DateTime
    {
        return $this->fromString('2013-01-01');
    }

    /**
     * {@inheritdoc}
     */
    public function fromString(string $datetime): \DateTime
    {
        return new \DateTime($datetime, new \DateTimeZone('UTC'));
    }

    /**
     * {@inheritdoc}
     */
    public function removeOneHour(\DateTime $datetime): \DateTime
    {
        return $datetime->sub(new \DateInterval('PT1H'));
    }
}
