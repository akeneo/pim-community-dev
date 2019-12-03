<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Symfony\Command\QualityHighlights;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Application\QualityHighlights\PushStructureAndProductsToFranklin;
use Akeneo\Pim\Automation\FranklinInsights\Application\QualityHighlights\SynchronizeAttributesWithFranklin;
use Akeneo\Pim\Automation\FranklinInsights\Application\QualityHighlights\SynchronizeFamiliesWithFranklin;
use Akeneo\Pim\Automation\FranklinInsights\Application\QualityHighlights\SynchronizeProductsWithFranklin;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\BatchSize;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PushStructureAndProductsToFranklinCommand extends Command
{
    private const NAME = 'pimee:franklin-insights:quality-highlights:push-structure-and-products';

    private const DEFAULT_BATCH_ATTRIBUTE_SIZE = 10;
    private const DEFAULT_BATCH_FAMILY_SIZE = 10;
    private const DEFAULT_BATCH_PRODUCT_SIZE = 500;

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

    /** @var PushStructureAndProductsToFranklin */
    private $pushStructureAndProductsToFranklin;

    public function __construct(
        PendingItemsRepositoryInterface $pendingItemsRepository,
        SynchronizeFamiliesWithFranklin $synchronizeFamilies,
        SynchronizeAttributesWithFranklin $synchronizeAttributes,
        SynchronizeProductsWithFranklin $synchronizeProductsWithFranklin,
        GetConnectionStatusHandler $connectionStatusHandler,
        PushStructureAndProductsToFranklin $pushStructureAndProductsToFranklin
    ) {
        parent::__construct(self::NAME);

        $this->pendingItemsRepository = $pendingItemsRepository;
        $this->synchronizeFamilies = $synchronizeFamilies;
        $this->synchronizeAttributes = $synchronizeAttributes;
        $this->synchronizeProductsWithFranklin = $synchronizeProductsWithFranklin;
        $this->connectionStatusHandler = $connectionStatusHandler;
        $this->pushStructureAndProductsToFranklin = $pushStructureAndProductsToFranklin;
    }

    protected function configure()
    {
        $this->setName(self::NAME)
            ->setDescription('Push catalog structure and products to Franklin API endpoints in order to compute Quality Highlights')
            ->addOption('batch-attributes', 'a', InputOption::VALUE_OPTIONAL, 'Number of attributes type entity to push in one HTTP call', self::DEFAULT_BATCH_ATTRIBUTE_SIZE)
            ->addOption('batch-families', 'f', InputOption::VALUE_OPTIONAL, 'Number of families type entity to push in one HTTP call', self::DEFAULT_BATCH_FAMILY_SIZE)
            ->addOption('batch-products', 'p', InputOption::VALUE_OPTIONAL, 'Number of products type entity to push in one HTTP call', self::DEFAULT_BATCH_PRODUCT_SIZE);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $batchAttributesSize = filter_var($input->getOption('batch-attributes'), FILTER_VALIDATE_INT);
        $batchAttributesSize = false === $batchAttributesSize ? self::DEFAULT_BATCH_ATTRIBUTE_SIZE : intval($batchAttributesSize);

        $batchFamiliesSize = filter_var($input->getOption('batch-families'), FILTER_VALIDATE_INT);
        $batchFamiliesSize = false === $batchFamiliesSize ? self::DEFAULT_BATCH_FAMILY_SIZE : intval($batchFamiliesSize);

        $batchProductsSize = filter_var($input->getOption('batch-products'), FILTER_VALIDATE_INT);
        $batchProductsSize = false === $batchProductsSize ? self::DEFAULT_BATCH_PRODUCT_SIZE : intval($batchProductsSize);

        $io = new SymfonyStyle($input, $output);

        if ($this->isFranklinInsightsActivated() === false) {
            $io->error('Unable to find an active Franklin configuration. Did you correctly set you Franklin Token in the PIM system tab ?');
            exit(1);
        }

        $io->title('Push catalog structure and products to Franklin API');

        $this->pushStructureAndProductsToFranklin->push(
            new BatchSize($batchAttributesSize),
            new BatchSize($batchFamiliesSize),
            new BatchSize($batchProductsSize)
        );
    }

    private function isFranklinInsightsActivated(): bool
    {
        $connectionStatus = $this->connectionStatusHandler->handle(new GetConnectionStatusQuery(false));
        return $connectionStatus->isActive();
    }
}
