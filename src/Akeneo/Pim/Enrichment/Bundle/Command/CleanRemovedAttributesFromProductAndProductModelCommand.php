<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductModelsWithRemovedAttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductsAndProductModelsWithInheritedRemovedAttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductsWithRemovedAttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Structure\Bundle\Event\AttributeEvents;
use Akeneo\Pim\Structure\Bundle\Manager\AttributeCodeBlacklister;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\GetAllBlacklistedAttributeCodesInterface;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\PimDataGridBundle\Normalizer\IdEncoder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Process\Process;
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
    private const JOB_TRACKER_ROUTE = 'akeneo_job_process_tracker_details';

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
    private RouterInterface $router;
    private string $pimUrl;
    private GetAllBlacklistedAttributeCodesInterface $getAllBlacklistedAttributeCodes;
    private AttributeCodeBlacklister $attributeCodeBlacklister;

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
        RouterInterface $router,
        string $pimUrl,
        GetAllBlacklistedAttributeCodesInterface $getAllBlacklistedAttributeCodes,
        AttributeCodeBlacklister $attributeCodeBlacklister
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
        $this->getAllBlacklistedAttributeCodes = $getAllBlacklistedAttributeCodes;
        $this->attributeCodeBlacklister = $attributeCodeBlacklister;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Removes all values of deleted attributes on all products and product models')
            ->addOption('all-blacklisted-attributes', InputArgument::OPTIONAL)
            ->addArgument('attributes', InputArgument::OPTIONAL | InputArgument::IS_ARRAY);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Clean removed attributes values');

        $shouldCleanAllBlacklistedAttributes = $input->getOption('all-blacklisted-attributes');
        $attributeCodesToClean = $input->getArgument('attributes');
        $allBlacklistedAttributeCodes = $this->getAllBlacklistedAttributeCodes->execute();

        if ($shouldCleanAllBlacklistedAttributes && !empty($attributeCodesToClean)) {
            $io->writeln(
                '<error>You cannot specify attribute codes when using the --all-blacklisted-attributes option.</error>'
            );

            return 1;
        }

        if ($shouldCleanAllBlacklistedAttributes) {
            if (empty($allBlacklistedAttributeCodes)) {
                $io->writeln('<info>There was no blacklisted attributes to clean.</info>');
                $io->writeln('Nothing to do.');

                return 0;
            }

            $io->writeln('Here is the list of blacklisted attributes that will be cleaned:');
            $io->listing($allBlacklistedAttributeCodes);

            $this->launchCleanRemovedAttributeJob($io, $allBlacklistedAttributeCodes);

            return 0;
        }

        if (!empty($attributeCodesToClean)) {
            if (!$this->checkBlacklistedAttributeCodesToCleanAllExist(
                $io,
                $attributeCodesToClean,
                $allBlacklistedAttributeCodes
            )) {
                return 0;
            }

            $this->launchCleanRemovedAttributeJob($io, $attributeCodesToClean);

            return 0;
        }

        $answer = $io->confirm(
            'This command will remove all values of deleted attributes on all products and product models' . "\n" .
                'Do you want to proceed?',
            true
        );

        if (!$answer) {
            $io->text('That\'s ok, see you!');

            return 0;
        }

        $io->text([
            'Ok, let\'s go!',
            '(If you see warnings appearing in the console output, it\'s totally normal as ',
            'the goal of the command is to avoid those warnings in the future)',
        ]);
        $io->newLine(2);

        $this->cleanAllProductsAndProductModels($output, $input, $io);
        $this->purgeCleanedBlackListedAttributes($allBlacklistedAttributeCodes);

        return 0;
    }

    /**
     * Get products
     */
    private function getProducts(ProductQueryBuilderFactoryInterface $pqbFactory): CursorInterface
    {
        $pqb = $pqbFactory->create();

        return $pqb->execute();
    }

    /**
     * Iterate over given products to launch clean commands
     */
    private function cleanProducts(
        CursorInterface $products,
        ProgressBar $progressBar,
        int $productBatchSize,
        EntityManagerClearerInterface $cacheClearer,
        string $env,
        string $rootDir
    ): void {
        $progressBar->start();
        $productIds = [];

        $productToCleanCount = 0;
        foreach ($products as $product) {
            $productIds[] = IdEncoder::encode(
                $product instanceof ProductModel ? 'product_model' : 'product',
                $product instanceof ProductModel ? $product->getId() : $product->getUuid()->toString()
            );
            $productToCleanCount++;
            if (0 === $productToCleanCount % $productBatchSize) {
                $this->launchCleanTask($productIds, $env, $rootDir);
                $cacheClearer->clear();
                $productIds = [];

                $progressBar->advance($productBatchSize);
            }
        }
        if (count($productIds) > 0) {
            $this->launchCleanTask($productIds, $env, $rootDir);
        }

        $progressBar->finish();
    }

    /**
     * Launches the clean command on given ids
     */
    private function launchCleanTask(array $productIds, string $env, string $rootDir)
    {
        $process = new Process(
            [
                sprintf('%s/../bin/console', $rootDir),
                'pim:product:refresh',
                sprintf('--env=%s', $env),
                implode(',', $productIds),
            ]
        );
        $process->setTimeout(null);
        $process->run();
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
            "This command will launch a job to remove the values of the attributes:\n" .
                "%s\n" .
                " This will update:\n" .
                " - %d product model(s) (and %d product variant(s))\n" .
                " - %d product(s)\n" .
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

        $this->eventDispatcher->dispatch(new GenericEvent(), AttributeEvents::POST_CLEAN);
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

    /**
     * @param OutputInterface $output
     * @param InputInterface $input
     * @param SymfonyStyle $io
     *
     */
    protected function cleanAllProductsAndProductModels(
        OutputInterface $output,
        InputInterface $input,
        SymfonyStyle $io
    ): void {
        $products = $this->getProducts($this->productQueryBuilderFactory);

        $progressBar = new ProgressBar($output, count($products));

        $env = $input->getOption('env');
        $this->cleanProducts(
            $products,
            $progressBar,
            $this->productBatchSize,
            $this->entityManagerClearer,
            $env,
            $this->kernelRootDir
        );
        $io->newLine();
        $io->text(sprintf('%d products well cleaned', $products->count()));

        $this->eventDispatcher->dispatch(new GenericEvent(), AttributeEvents::POST_CLEAN);
    }

    private function purgeCleanedBlackListedAttributes(array $allBlacklistedAttributeCodes)
    {
        foreach ($allBlacklistedAttributeCodes as $blacklistedAttributeCode) {
            $this->attributeCodeBlacklister->removeFromBlacklist($blacklistedAttributeCode);
        }
    }
}
