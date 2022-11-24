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
            'Akeneo\Platform\JobAutomation\Domain',
            'Akeneo\Platform\Bundle\ImportExportBundle\Domain'
        ],
    )->in('Akeneo\Platform\JobAutomation\Application'),
    $builder->only(
        [
            'Cron\CronExpression',
            'Webmozart\Assert\Assert',
            'Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model',
        ],
    )->in('Akeneo\Platform\JobAutomation\Domain'),
    $builder->only(
        [
            'Doctrine\DBAL\Connection',
            'Doctrine\DBAL\Types\Types',
            'Symfony\Component',
            'Symfony\Contract',
            'Psr\Log\LoggerInterface',
            'phpseclib3\Crypt\RSA',

            'Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag',
            'Akeneo\Platform\Bundle\ImportExportBundle\Domain',
            'Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure',
            'Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents',
            'Akeneo\Platform\Bundle\NotificationBundle',
            'Akeneo\Platform\JobAutomation\Domain',
            'Akeneo\Platform\JobAutomation\Application',
            'Akeneo\Tool\Bundle\BatchBundle\Validator\Constraints\Automation',
            'Akeneo\Tool\Bundle\BatchBundle\Validator\Constraints\Scheduling',
            'Akeneo\Tool\Component\Batch\Event\EventInterface',
            'Akeneo\Tool\Component\Batch\Event\JobExecutionEvent',
            'Akeneo\Tool\Component\Batch\Job',
            'Akeneo\Tool\Component\Batch\Model\JobInstance',
            'Akeneo\Tool\Component\Batch\Model\StepExecution',
            'Akeneo\Tool\Component\Batch\Exception\InvalidJobException',
            'Akeneo\Tool\Component\BatchQueue\Queue\PublishJobToQueue',
            'Akeneo\Tool\Component\Connector\Step\TaskletInterface',
            'Akeneo\UserManagement\ServiceApi',

            'League\Flysystem\Filesystem',
            'League\Flysystem\PhpseclibV3',
            'Twig\Environment',
        ],
    )->in('Akeneo\Platform\JobAutomation\Infrastructure'),
];

return new Configuration($rules, $finder);
