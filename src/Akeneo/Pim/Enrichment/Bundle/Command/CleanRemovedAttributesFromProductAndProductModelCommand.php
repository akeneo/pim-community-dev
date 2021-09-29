<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductModelsWithRemovedAttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductsAndProductModelsWithInheritedRemovedAttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductsWithRemovedAttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Structure\Bundle\Event\AttributeEvents;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\FindBlacklistedAttributesCodesInterface;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\User;

/**
 * Removes all values of deleted attributes on all products and product models
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CleanRemovedAttributesFromProductAndProductModelCommand extends Command
{
    protected static $defaultName = 'pim:product:clean-removed-attributes';
    private const JOB_NAME = 'clean_removed_attribute_job';
    private const JOB_TRACKER_ROUTE = 'pim_enrich_job_tracker_show';

    private EntityManagerClearerInterface $entityManagerClearer;
    private ProductQueryBuilderFactoryInterface $productQueryBuilderFactory;
    private string $kernelRootDir;
    private int $productBatchSize;
    private EventDispatcherInterface $eventDispatcher;
    private JobLauncherInterface $jobLauncher;
    private IdentifiableObjectRepositoryInterface $jobInstanceRepository;
    private CountProductsWithRemovedAttributeInterface $countProductsWithRemovedAttribute;
    private CountProductModelsWithRemovedAttributeInterface $countProductModelsWithRemovedAttribute;
    private CountProductsAndProductModelsWithInheritedRemovedAttributeInterface $countProductsAndProductModelsWithInheritedRemovedAttribute;
    private FindBlacklistedAttributesCodesInterface $findBlacklistedAttributesCodes;
    private RouterInterface $router;
    private string $pimUrl;

    public function __construct(
        EntityManagerClearerInterface $entityManagerClearer,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        string $kernelRootDir,
        int $productBatchSize,
        EventDispatcherInterface $eventDispatcher,
        JobLauncherInterface $jobLauncher,
        IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        CountProductsWithRemovedAttributeInterface $countProductsWithRemovedAttribute,
        CountProductModelsWithRemovedAttributeInterface $countProductModelsWithRemovedAttribute,
        CountProductsAndProductModelsWithInheritedRemovedAttributeInterface $countProductsAndProductModelsWithInheritedRemovedAttribute,
        FindBlacklistedAttributesCodesInterface $findBlacklistedAttributesCodes,
        RouterInterface $router,
        string $pimUrl
    ) {
        parent::__construct();

        $this->entityManagerClearer = $entityManagerClearer;
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
        $this->kernelRootDir = $kernelRootDir;
        $this->productBatchSize = $productBatchSize;
        $this->eventDispatcher = $eventDispatcher;
        $this->jobLauncher = $jobLauncher;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->countProductsWithRemovedAttribute = $countProductsWithRemovedAttribute;
        $this->countProductModelsWithRemovedAttribute = $countProductModelsWithRemovedAttribute;
        $this->countProductsAndProductModelsWithInheritedRemovedAttribute = $countProductsAndProductModelsWithInheritedRemovedAttribute;
        $this->router = $router;
        $this->pimUrl = $pimUrl;
        $this->findBlacklistedAttributesCodes = $findBlacklistedAttributesCodes;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Removes all values of deleted attributes on all products and product models')
            ->addArgument('attributes', InputArgument::OPTIONAL | InputArgument::IS_ARRAY)
            ->addOption(
                'all',
                null,
                InputOption::VALUE_NONE,
                'If defined, all blacklisted attributes will be processed.'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Clean removed attributes values');

        $shouldCleanAll = $input->getOption('all');
        $attributeCodesToClean = $input->getArgument('attributes');
        $allBlacklistedAttributeCodes = $this->findBlacklistedAttributesCodes->all();


        if ($shouldCleanAll && !empty($attributeCodesToClean)) {
            $io->writeln('<error>You cannot specify attribute codes when using the --all option.</error>');

            return 1;
        }

        if (!$shouldCleanAll && empty($attributeCodesToClean)) {
            $io->writeln(
                '<info>Please specify a list of attribute codes to clean or use the --all option to process all blacklisted attributes.</info>'
            );
            $io->writeln('<info>Nothing to do.</info>');

            return 1;
        }

        if (!$this->checkBlacklistedAttributeCodesToCleanAllExist(
            $io,
            $attributeCodesToClean,
            $allBlacklistedAttributeCodes
        )) {
            return 0;
        }

        if ($shouldCleanAll) {
            if (empty($allBlacklistedAttributeCodes)) {
                $io->writeln('<info>There was no blacklisted attributes to process.</info>');
                $io->writeln('<info>Nothing to do.</info>');

                return 0;
            }

            $this->launchCleanRemovedAttributeJob($io, $allBlacklistedAttributeCodes);

            return 0;
        }

        $this->launchCleanRemovedAttributeJob($io, $attributeCodesToClean);

        return 0;
    }

    /**
     * Launches the Cleaning removed attribute values and display a link to its execution in the process tracker
     */
    private function launchCleanRemovedAttributeJob(SymfonyStyle $io, array $attributeCodes): void
    {
        $countProducts = $this->countProductsWithRemovedAttribute->count($attributeCodes);
        $countProductModels = $this->countProductModelsWithRemovedAttribute->count($attributeCodes);
        $countProductVariants = $this->countProductsAndProductModelsWithInheritedRemovedAttribute->count(
            $attributeCodes
        );

        $confirmMessage = sprintf(
            "This command will launch a job to remove the values of the attributes:\n".
            "%s\n".
            " This will update:\n".
            " - %d product model(s) (and %d product variant(s))\n".
            " - %d product(s)\n".
            " Do you want to proceed?",
            implode(
                array_map(function (string $attributeCode) {
                    return sprintf(" - %s\n", $attributeCode);
                }, $attributeCodes)
            ),
            $countProductModels,
            $countProductVariants,
            $countProducts
        );

        $answer = $io->confirm($confirmMessage, true);

        if (!$answer) {
            return;
        }

        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier(self::JOB_NAME);
        $jobExecution = $this->jobLauncher->launch($jobInstance, new User(UserInterface::SYSTEM_USER_NAME, null), [
            'attribute_codes' => $attributeCodes,
        ]);

        $jobUrl = sprintf(
            '%s/#%s',
            $this->pimUrl,
            $this->router->generate(self::JOB_TRACKER_ROUTE, ['id' => $jobExecution->getId()])
        );

        $io->text(
            sprintf(
                'The cleaning removed attribute values job has been launched, you can follow its progression here: %s',
                $jobUrl
            )
        );

        $this->eventDispatcher->dispatch(AttributeEvents::POST_CLEAN);
    }

    private function checkBlacklistedAttributeCodesToCleanAllExist(
        SymfonyStyle $io,
        array $attributeCodesToClean,
        array $allBlacklistedAttributeCodes
    ): bool {
        $nonExistingBlacklistedAttributeCodes = array_diff($attributeCodesToClean, $allBlacklistedAttributeCodes);
        if (!empty($nonExistingBlacklistedAttributeCodes)) {
            $io->writeln('<error>The following attribute codes do not exist in the Blacklist:</error>');
            $io->listing($nonExistingBlacklistedAttributeCodes);

            return false;
        }

        return true;
    }
}
