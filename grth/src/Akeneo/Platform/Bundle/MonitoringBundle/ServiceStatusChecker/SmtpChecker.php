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

namespace Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker;

use Exception;

final class SmtpChecker
{
    private \Swift_Transport $transport;

    public function __construct(\Swift_Transport $transport)
    {
        $this->transport = $transport;
    }

    public function status(): ServiceStatus
    {
        try {
            $ping = $this->transport->ping();
        } catch (Exception $e) {
            return ServiceStatus::notOk(sprintf('Unable to ping the mailer transport: "%s".', $e->getMessage()));
        }

        return $ping ? ServiceStatus::ok() : ServiceStatus::notOk('Unable to ping the mailer transport.');
    }
}
