<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Symfony\Command\QualityHighlights;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class InitializeStructureAndProductToPushToFranklinCommand extends Command
{
    private const NAME = 'pimee:franklin-insights:quality-highlights:initialize-structure-and-products';

    /** @var GetConnectionStatusHandler */
    private $connectionStatusHandler;

    /** @var PendingItemsRepositoryInterface */
    private $pendingItemsRepository;

    public function __construct(
        GetConnectionStatusHandler $connectionStatusHandler,
        PendingItemsRepositoryInterface $pendingItemsRepository
    ) {
        parent::__construct(self::NAME);
        $this->connectionStatusHandler = $connectionStatusHandler;
        $this->pendingItemsRepository = $pendingItemsRepository;
    }

    protected function configure()
    {
        $this->setName(self::NAME)
            ->setDescription('Retrieve all the attributes, families and product and add them to the pending item table. Those entities will be pushed to Franklin in a second time.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Initialize the Quality Highlights entities to push to Franklin');

        if ($this->isFranklinInsightsActivated() === false) {
            $io->error('Unable to find an active Franklin configuration. Did you correctly set you Franklin Token in the PIM system tab ?');
            exit(1);
        }

        $io->section('Initialize the Attributes');
        $this->pendingItemsRepository->fillWithAllAttributes();

        $io->section('Initialize the Families');
        $this->pendingItemsRepository->fillWithAllFamilies();

        $io->section('Initialize the Products');
        $this->pendingItemsRepository->fillWithAllProducts();

        $io->writeln('<info>Everything went fine, the entities needed by the Quality Highlights have been initialized.</info>');

        $io->note('You still have to setup the CRON in order to push those data to Franklin: bin/console pimee:franklin-insights:quality-highlights:push-structure-and-products');
    }

    private function isFranklinInsightsActivated(): bool
    {
        $connectionStatus = $this->connectionStatusHandler->handle(new GetConnectionStatusQuery(false));

        return $connectionStatus->isActive();
    }
}
