<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$builder = new RuleBuilder();

$rules = [
    $builder->only(
        [
            'Webmozart\Assert\Assert',
            'Akeneo\Platform\JobAutomation\Domain',
        ],
    )->in('Akeneo\Platform\JobAutomation\Application'),
    $builder->only(
        [
            'Webmozart\Assert\Assert',
            'Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model',
        ],
    )->in('Akeneo\Platform\JobAutomation\Domain'),
    $builder->only(
        [
            'Symfony\Component',

            'Akeneo\Platform\Bundle\ImportExportBundle\Domain',
            'Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure',
            'Akeneo\Platform\JobAutomation\Domain\Model\SftpStorage',

            'League\Flysystem\Filesystem',
            'League\Flysystem\PhpseclibV2',
        ],
    )->in('Akeneo\Platform\JobAutomation\Infrastructure'),
];

return new Configuration($rules, $finder);
