<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Symfony\Command\QualityHighlights;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Application\QualityHighlights\SynchronizeAttributesWithFranklin;
use Akeneo\Pim\Automation\FranklinInsights\Application\QualityHighlights\SynchronizeFamiliesWithFranklin;
use Akeneo\Pim\Automation\FranklinInsights\Application\QualityHighlights\SynchronizeProductsWithFranklin;
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

    private const DEFAULT_BATCH_SIZE = 10;

    /** @var PendingItemsRepositoryInterface */
    private $pendingItemsRepository;

    /** @var SynchronizeFamiliesWithFranklin */
    private $synchronizeFamilies;

    /** @var SynchronizeAttributesWithFranklin */
    private $synchronizeAttributes;

    /** @var SynchronizeProductsWithFranklin */
    private $synchronizeProductsWithFranklin;

    /** @var GetConnectionStatusHandler */
    private $connectionStatusHandler;

    public function __construct(
        PendingItemsRepositoryInterface $pendingItemsRepository,
        SynchronizeFamiliesWithFranklin $synchronizeFamilies,
        SynchronizeAttributesWithFranklin $synchronizeAttributes,
        SynchronizeProductsWithFranklin $synchronizeProductsWithFranklin,
        GetConnectionStatusHandler $connectionStatusHandler
    ) {
        parent::__construct(self::NAME);

        $this->pendingItemsRepository = $pendingItemsRepository;
        $this->synchronizeFamilies = $synchronizeFamilies;
        $this->synchronizeAttributes = $synchronizeAttributes;
        $this->synchronizeProductsWithFranklin = $synchronizeProductsWithFranklin;
        $this->connectionStatusHandler = $connectionStatusHandler;
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
        $batchSize = false === $batchSize ? self::DEFAULT_BATCH_SIZE : intval($batchSize);

        $io = new SymfonyStyle($input, $output);

        if ($this->isFranklinInsightsActivated() === false) {
            $io->error('Unable to find an active Franklin configuration. Did you correctly set you Franklin Token in the PIM system tab ?');
            exit(1);
        }

        $io->title('Push catalog structure and products to Franklin API');

        $lock = new Lock((Uuid::uuid4())->toString());
        $this->pendingItemsRepository->acquireLock($lock);

        //The following order is important and must not be changed Attributes, then Families, then products.
        $io->section('Synchronize Attributes');
        $this->synchronizeAttributes->synchronize($lock, $batchSize);
        $io->section('Synchronize Families');
        $this->synchronizeFamilies->synchronize($lock, $batchSize);
        $io->section('Synchronize Products');
        $this->synchronizeProductsWithFranklin->synchronize($lock, $batchSize);
    }

    private function isFranklinInsightsActivated(): bool
    {
        $connectionStatus = $this->connectionStatusHandler->handle(new GetConnectionStatusQuery(false));
        return $connectionStatus->isActive();
    }
}
