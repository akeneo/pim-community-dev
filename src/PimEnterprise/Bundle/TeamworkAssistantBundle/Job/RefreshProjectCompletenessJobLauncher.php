<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\TeamworkAssistantBundle\Job;

use Akeneo\Bundle\BatchBundle\Job\JobInstanceRepository;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * Launch the attribute group completeness calculation job
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class RefreshProjectCompletenessJobLauncher
{
    /** @var JobInstanceRepository */
    protected $jobInstanceRepository;

    /** @var string */
    protected $jobName;

    /** @var string */
    protected $rootDirectory;

    /** @var string */
    protected $environment;

    /** @var string */
    protected $logsDir;

    /**
     * @param JobInstanceRepository $jobInstanceRepository
     * @param string                $jobName
     * @param string                $rootDirectory
     * @param string                $environment
     * @param string                $logsDir
     */
    public function __construct(
        JobInstanceRepository $jobInstanceRepository,
        string $jobName,
        string $rootDirectory,
        string $environment,
        string $logsDir
    ) {
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->jobName = $jobName;
        $this->rootDirectory = $rootDirectory;
        $this->environment = $environment;
        $this->logsDir = $logsDir;
    }

    /**
     * @param ProductInterface $product
     * @param string           $channel
     * @param string           $locale
     */
    public function launch(ProductInterface $product, $channel, $locale)
    {
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($this->jobName);
        if (null === $jobInstance) {
            throw new \RuntimeException('Cannot run refresh project completeness, there is no available job');
        }

        $configuration = [
            'product_identifier' => $product->getId(),
            'channel_identifier' => $channel,
            'locale_identifier'  => $locale,
        ];
        $encodedConfiguration = json_encode($configuration, JSON_HEX_APOS);

        $pathFinder = new PhpExecutableFinder();
        $command = sprintf(
            '%s %s%s..%sbin%sconsole akeneo:batch:job --env=%s %s %s >> %s%sbatch_execute.log 2>&1 &',
            $pathFinder->find(),
            $this->rootDirectory,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            $this->environment,
            escapeshellarg($jobInstance->getCode()),
            sprintf('--config=%s', escapeshellarg($encodedConfiguration)),
            $this->logsDir,
            DIRECTORY_SEPARATOR
        );

        exec($command);
    }
}
