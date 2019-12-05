<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Symfony\Command\QualityHighlights;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionIsActiveHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionIsActiveQuery;
use Akeneo\Pim\Automation\FranklinInsights\Application\QualityHighlights\PushStructureAndProductsToFranklin;
use Akeneo\Pim\Automation\FranklinInsights\Application\QualityHighlights\SchedulePushStructureAndProductsToFranklinInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\BatchSize;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PushStructureAndProductsToFranklinCommand extends Command
{
    private const NAME = 'pimee:franklin-insights:quality-highlights:push-structure-and-products';

    /** @var GetConnectionIsActiveHandler */
    private $connectionStatusHandler;

    /** @var SchedulePushStructureAndProductsToFranklinInterface */
    private $schedulePushStructureAndProductsToFranklin;

    public function __construct(
        GetConnectionIsActiveHandler $connectionIsActiveHandler,
        SchedulePushStructureAndProductsToFranklinInterface $schedulePushStructureAndProductsToFranklin
    ) {
        parent::__construct(self::NAME);

        $this->connectionStatusHandler = $connectionIsActiveHandler;
        $this->schedulePushStructureAndProductsToFranklin = $schedulePushStructureAndProductsToFranklin;
    }

    protected function configure()
    {
        $this->setName(self::NAME)
            ->setDescription('Schedule a push of catalog structure and products to Franklin API endpoints in order to compute Quality Highlights')
            ->addOption('batch-attributes', 'a', InputOption::VALUE_OPTIONAL, 'Number of attributes type entity to push in one HTTP call', PushStructureAndProductsToFranklin::DEFAULT_ATTRIBUTES_BATCH_SIZE)
            ->addOption('batch-families', 'f', InputOption::VALUE_OPTIONAL, 'Number of families type entity to push in one HTTP call', PushStructureAndProductsToFranklin::DEFAULT_FAMILIES_BATCH_SIZE)
            ->addOption('batch-products', 'p', InputOption::VALUE_OPTIONAL, 'Number of products type entity to push in one HTTP call', PushStructureAndProductsToFranklin::DEFAULT_PRODUCTS_BATCH_SIZE);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $batchAttributesSize = filter_var($input->getOption('batch-attributes'), FILTER_VALIDATE_INT);
        $batchAttributesSize = false === $batchAttributesSize ? PushStructureAndProductsToFranklin::DEFAULT_ATTRIBUTES_BATCH_SIZE : intval($batchAttributesSize);

        $batchFamiliesSize = filter_var($input->getOption('batch-families'), FILTER_VALIDATE_INT);
        $batchFamiliesSize = false === $batchFamiliesSize ? PushStructureAndProductsToFranklin::DEFAULT_FAMILIES_BATCH_SIZE : intval($batchFamiliesSize);

        $batchProductsSize = filter_var($input->getOption('batch-products'), FILTER_VALIDATE_INT);
        $batchProductsSize = false === $batchProductsSize ? PushStructureAndProductsToFranklin::DEFAULT_PRODUCTS_BATCH_SIZE : intval($batchProductsSize);

        $io = new SymfonyStyle($input, $output);

        if ($this->isFranklinInsightsActivated() === false) {
            $io->error('Unable to find an active Franklin configuration. Did you correctly set you Franklin Token in the PIM system tab ?');
            exit(1);
        }

        $this->schedulePushStructureAndProductsToFranklin->schedule(
            new BatchSize($batchAttributesSize),
            new BatchSize($batchFamiliesSize),
            new BatchSize($batchProductsSize)
        );

        $io->title('A push catalog structure and products to Franklin API has been scheduled.');
    }

    private function isFranklinInsightsActivated(): bool
    {
        return $this->connectionStatusHandler->handle(new GetConnectionIsActiveQuery());
    }
}
