<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Symfony\Command\QualityHighlights;

use Akeneo\Pim\Automation\FranklinInsights\Application\QualityHighlights\SynchronizeAttributesWithFranklin;
use Akeneo\Pim\Automation\FranklinInsights\Application\QualityHighlights\SynchronizeFamiliesWithFranklin;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\Lock;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PushStructureAndProductsToFranklinCommand extends Command
{
    private const NAME = 'pimee:franklin-insights:quality-highlights:push-structure-and-products';

    private const DEFAULT_BATCH_SIZE = 100;

    /** @var PendingItemsRepositoryInterface */
    private $pendingItemsRepository;

    /** @var SynchronizeFamiliesWithFranklin */
    private $synchronizeFamilies;

    /** @var SynchronizeAttributesWithFranklin */
    private $synchronizeAttributes;

    public function __construct(
        PendingItemsRepositoryInterface $pendingItemsRepository,
        SynchronizeFamiliesWithFranklin $synchronizeFamilies,
        SynchronizeAttributesWithFranklin $synchronizeAttributes
    ) {
        parent::__construct(self::NAME);

        $this->pendingItemsRepository = $pendingItemsRepository;
        $this->synchronizeFamilies = $synchronizeFamilies;
        $this->synchronizeAttributes = $synchronizeAttributes;
    }

    protected function configure()
    {
        $this->setName(self::NAME)
            ->setDescription('Push catalog structure and products to Franklin API endpoints in order to compute Quality Highlights')
            ->addOption('batch', 'b', InputOption::VALUE_OPTIONAL, 'Send the entities by batch', self::DEFAULT_BATCH_SIZE);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $batchSize = filter_var($input->getOption('batch'), FILTER_VALIDATE_INT);

        if ($batchSize === false) {
            $batchSize = self::DEFAULT_BATCH_SIZE;
        }

        $io = new SymfonyStyle($input, $output);

        $io->title('Push catalog structure and products to Franklin API');

        $lock = new Lock((Uuid::uuid4())->toString());
        $this->pendingItemsRepository->acquireLock($lock);

        //The following order is important and must not be changed Attributes, then Families, then products.
        $io->section('Synchronize Attributes');
        $this->synchronizeAttributes->synchronize($lock, (int) $batchSize);
        $io->section('Synchronize Families');
        $this->synchronizeFamilies->synchronize($lock, (int) $batchSize);

        $io->section('Synchronize Products (TODO)');
        //TODO synchronize products
    }
}
