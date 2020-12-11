<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Structure\Bundle\Event\AttributeEvents;
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
    private const JOB_TRACKER_ROUTE = 'pim_enrich_job_tracker_show';

    private EntityManagerClearerInterface $entityManagerClearer;
    private ProductQueryBuilderFactoryInterface $productQueryBuilderFactory;
    private string $kernelRootDir;
    private int $productBatchSize;
    private EventDispatcherInterface $eventDispatcher;
    private JobLauncherInterface $jobLauncher;
    private IdentifiableObjectRepositoryInterface $jobInstanceRepository;
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
        $this->router = $router;
        $this->pimUrl = $pimUrl;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Removes all values of deleted attributes on all products and product models')
            ->addArgument('attributes', InputArgument::OPTIONAL | InputArgument::IS_ARRAY);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Clean removed attributes values');

        $attributeCodes = $input->getArgument('attributes');

        if (!empty($attributeCodes)) {
            $this->launchCleanRemovedAttributeJob($io, $attributeCodes);

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
            'the goal of the command is to avoid those warnings in the future)'
        ]);
        $io->newLine(2);

        $products = $this->getProducts($this->productQueryBuilderFactory);

        $progressBar = new ProgressBar($output, count($products));

        $env = $input->getOption('env');
        $this->cleanProducts($products, $progressBar, $this->productBatchSize, $this->entityManagerClearer, $env, $this->kernelRootDir);
        $io->newLine();
        $io->text(sprintf('%d products well cleaned', $products->count()));

        $this->eventDispatcher->dispatch(AttributeEvents::POST_CLEAN);

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
            $productIds[] = IdEncoder::encode($product instanceof ProductModel ? 'product_model' : 'product', $product->getId());
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
        $process = new Process([sprintf('%s/../bin/console', $rootDir), 'pim:product:refresh', sprintf('--env=%s', $env), implode(',', $productIds)]);
        $process->setTimeout(null);
        $process->run();
    }

    /**
     * Launches the clean removed attribute job and display a link to its execution in the process tracker
     */
    private function launchCleanRemovedAttributeJob(SymfonyStyle $io, array $attributeCodes): void
    {
        $confirmMessage = sprintf(
            "This command will launch a job to remove the values of the attributes:\n" .
                '%s' .
                ' Do you want to proceed?',
            implode(array_map(function (string $attributeCode) {
                return sprintf(" - %s\n", $attributeCode);
            }, $attributeCodes)),
        );

        $answer = $io->confirm($confirmMessage, true);

        if (!$answer) {
            return;
        }

        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier(self::JOB_NAME);
        $jobExecution = $this->jobLauncher->launch($jobInstance, new User(UserInterface::SYSTEM_USER_NAME, null), [
            'attribute_codes' => $attributeCodes
        ]);

        $jobUrl = sprintf(
            '%s/#%s',
            $this->pimUrl,
            $this->router->generate(self::JOB_TRACKER_ROUTE, ['id' => $jobExecution->getId()])
        );

        $io->text(sprintf(
            'The clean removed attribute job has been launched, you can follow its progression here: %s',
            $jobUrl
        ));
    }
}
