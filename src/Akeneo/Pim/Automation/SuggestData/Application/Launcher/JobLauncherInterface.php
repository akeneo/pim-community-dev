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

namespace Akeneo\Pim\Automation\SuggestData\Application\Launcher;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
interface JobLauncherInterface
{
    /**
     * @param string $jobInstanceName
     * @param array $options
     */
    public function launch(string $jobInstanceName, array $options = []): void;
}
