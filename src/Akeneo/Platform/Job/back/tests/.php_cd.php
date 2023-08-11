<?php

declare(strict_types=1);

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$finder->notPath('tests');
$builder = new RuleBuilder();

$rules = [
    $builder->only(
        [
            'Akeneo\Platform\Job\Domain',
            'Akeneo\Platform\Job\ServiceApi',
            'Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceFactory',
            'Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface',
            'Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\ManualUploadStorage',
            'Akeneo\Tool\Component\Batch\Exception\InvalidJobException',
            'Akeneo\Tool\Component\Batch\Job\JobInterface',
            'Akeneo\Tool\Component\Batch\Job\JobParameters',
            'Akeneo\Tool\Component\Batch\Job\JobParametersFactory',
            'Akeneo\Tool\Component\Batch\Job\JobRegistry',
            'Akeneo\Tool\Component\Batch\Model\JobInstance',
            'Akeneo\Tool\Component\BatchQueue\Queue\PublishJobToQueueInterface',
            'Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface',
            'Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface',
            'Symfony\Component\Validator\Validator\ValidatorInterface',
            'Webmozart\Assert\Assert',
        ],
    )->in('Akeneo\Platform\Job\Application'),
    $builder->only(
        [],
    )->in('Akeneo\Platform\Job\Domain'),
    $builder->only(
        [
            'Akeneo\Platform\Installer\Infrastructure\Event\InstallerEvents',
            'Akeneo\Platform\Job\Application',
            'Akeneo\Platform\Job\Domain',
            'Akeneo\Platform\Job\ServiceApi',
            'Akeneo\Tool\Component\Connector\Job\JobFileLocation',
            'League\Flysystem\FilesystemOperator',
            'Doctrine\DBAL\Connection',
            'Oro\Bundle\SecurityBundle\SecurityFacade',
            'Symfony\Component',
        ],
    )->in('Akeneo\Platform\Job\Infrastructure'),
];

return new Configuration($rules, $finder);
