<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\RuleEngine\Integration\Context;

use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Job\JobParametersFactory;
use Akeneo\Tool\Component\Batch\Job\JobParametersValidator;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Box\Spout\Common\Type;
use Box\Spout\Writer\WriterFactory;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class ImportProfilesContext implements Context
{
    /** @var JobRegistry */
    private $jobRegistry;

    /** @var JobParametersFactory */
    private $jobParametersFactory;

    /** @var JobParametersValidator */
    private $jobParametersValidator;

    /** @var SaverInterface */
    private $jobInstanceSaver;

    /** @var JobLauncher */
    protected $jobLauncherTest;

    /** @var JobLauncherInterface */
    protected $jobLauncher;

    /** @var JobInstanceRepository */
    private $jobInstanceRepository;

    /** @var UserProviderInterface */
    private $userProvider;

    /** @var EntityManagerClearerInterface */
    private $entityManagerClearer;

    /** @var string */
    private $kernelRootDir;

    /** @var null|string */
    private $filenameToImport = null;

    public function __construct(
        JobRegistry $jobRegistry,
        JobParametersFactory $jobParametersFactory,
        JobParametersValidator $jobParametersValidator,
        SaverInterface $jobInstanceSaver,
        JobLauncher $jobLauncherTest,
        JobLauncherInterface $jobLauncher,
        JobInstanceRepository $jobInstanceRepository,
        UserProviderInterface $userProvider,
        EntityManagerClearerInterface $entityManagerClearer,
        string $kernelRootDir
    ) {
        $this->jobRegistry = $jobRegistry;
        $this->jobParametersFactory = $jobParametersFactory;
        $this->jobParametersValidator = $jobParametersValidator;
        $this->jobInstanceSaver = $jobInstanceSaver;
        $this->jobLauncherTest = $jobLauncherTest;
        $this->jobLauncher = $jobLauncher;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->userProvider = $userProvider;
        $this->entityManagerClearer = $entityManagerClearer;
        $this->kernelRootDir = $kernelRootDir;
    }

    /**
     * @Given /^the following ([^"]*) file to import:$/
     */
    public function theFollowingFileToImport($extension, PyStringNode $string): void
    {
        $extension = strtolower($extension);

        $string = $this->replacePlaceholders($string->getRaw());

        $this->filenameToImport = sprintf(
            '%s/pim-import/behat-import-%s.%s',
            $this->getTmpDirectory(),
            substr(md5((string) rand()), 0, 7),
            $extension
        );
        @rmdir(dirname($this->filenameToImport));
        @mkdir(dirname($this->filenameToImport), 0777, true);

        if (Type::XLSX === $extension) {
            $writer = WriterFactory::create($extension);
            $writer->openToFile($this->filenameToImport);
            foreach (explode(PHP_EOL, $string) as $row) {
                $rowCells = explode(";", $row);
                foreach ($rowCells as &$cell) {
                    if (is_numeric($cell) && 0 === preg_match('|^\+[0-9]+$|', $cell)) {
                        $cell = false === strpos($cell, '.') ? (int) $cell : (float) $cell;
                    }
                }

                $writer->addRow($rowCells);
            }
            $writer->close();
        } else {
            file_put_contents($this->filenameToImport, (string) $string);
        }
    }

    /**
     * @Given /^the following job "([^"]*)" configuration:$/
     */
    public function theFollowingJobConfiguration($code, TableNode $table): void
    {
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($code);
        $configuration = $jobInstance->getRawParameters();

        foreach ($table->getRowsHash() as $property => $value) {
            $value = $this->replacePlaceholders($value);
            if (in_array($value, ['yes', 'no'])) {
                $value = 'yes' === $value;
            }

            if ('filters' === $property) {
                $value = json_decode($value, true);
            }

            $configuration[$property] = $value;
        }

        $job = $this->jobRegistry->get($jobInstance->getJobName());
        $jobParams = $this->jobParametersFactory->create($job, $configuration);
        $violations = $this->jobParametersValidator->validate($job, $jobParams, ['Default']);

        if ($violations->count() > 0) {
            $messages = [];
            foreach ($violations as $violation) {
                $messages[] = $violation->getMessage();
            }
            throw new \InvalidArgumentException(
                sprintf(
                    'The parameters "%s" are not valid for the job "%s" due to violations "%s"',
                    print_r($jobParams->all(), true),
                    $job->getName(),
                    implode(', ', $messages)
                )
            );
        }

        $jobInstance->setRawParameters($jobParams->all());
        $this->jobInstanceSaver->save($jobInstance);
    }

    /**
     * @When /^I launch the "([^"]*)" import job$/
     */
    public function iLaunchTheImportJob(string $jobIdentifier): void
    {
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($jobIdentifier);
        if (null === $jobInstance) {
            throw new \RuntimeException(sprintf('The "%s" job instance is not found.', $jobIdentifier));
        }

        $user = $this->userProvider->loadUserByUsername('admin');
        $jobExecution = $this->jobLauncher->launch($jobInstance, $user, []);
        $this->jobLauncherTest->launchConsumerOnce();
        $this->jobLauncherTest->waitCompleteJobExecution($jobExecution);
        $this->entityManagerClearer->clear();
    }

    public function replacePlaceholders(string $string): string
    {
        return strtr($string, [
            '%tmp%' => $this->getTmpDirectory(),
            '%fixtures%' => $this->kernelRootDir . '/../tests/legacy/features/Context/fixtures/',
            '%web%' => $this->kernelRootDir . '/../public/',
            '%file to import%' => $this->filenameToImport,
        ]);
    }

    private function getTmpDirectory(): string
    {
        return !empty($_ENV['BEHAT_TMPDIR'] ?? '') ? $_ENV['BEHAT_TMPDIR'] : '/tmp/pim-behat';
    }
}
