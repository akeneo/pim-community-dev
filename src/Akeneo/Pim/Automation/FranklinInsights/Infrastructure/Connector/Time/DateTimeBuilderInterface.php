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
interface DateTimeBuilderInterface
{
    /**
     * @return \DateTime
     */
    public function buildForFirstProductFetch(): \DateTime;

    /**
     * @param string $datetime
     *
     * @throws \Exception
     *
     * @return \DateTime
     */
    public function fromString(string $datetime): \DateTime;

    /**
     * @param \DateTime $datetime
     *
     * @return \DateTime
     */
    public function removeOneHour(\DateTime $datetime): \DateTime;
}
