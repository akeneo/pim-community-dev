<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Command;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ConsolidateDashboardRates;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ConsolidateProductAxisRates;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\DictionarySource;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\CreateProductsCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\EvaluatePendingCriteria;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\RanksDistributionCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\DashboardRatesProjection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ConsolidationDate;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker\AspellDictionaryGenerator;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\IndexProductRates;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\DashboardRatesProjectionRepository;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\FetchMode;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DemoHelperCommand extends Command
{
    /** @var DictionarySource */
    private $productValueInDatabaseDictionarySource;

    /** @var AspellDictionaryGenerator */
    private $aspellDictionaryGenerator;

    /** @var ConsolidateDashboardRates */
    private $consolidateDashboardRates;

    /** @var DashboardRatesProjectionRepository */
    private $dashboardRatesProjectionRepository;

    /** @var Connection */
    private $db;

    /** @var CreateProductsCriteriaEvaluations */
    private $createProductsCriteriaEvaluations;

    /** @var EvaluatePendingCriteria */
    private $evaluatePendingCriteria;

    /** @var ConsolidateProductAxisRates */
    private $consolidateProductAxisRates;

    /** @var IndexProductRates */
    private $indexProductRates;

    public function __construct(
        DictionarySource $productValueInDatabaseDictionarySource,
        AspellDictionaryGenerator $aspellDictionaryGenerator,
        ConsolidateDashboardRates $consolidateDashboardRates,
        DashboardRatesProjectionRepository $dashboardRatesProjectionRepository,
        Connection $db,
        CreateProductsCriteriaEvaluations $createProductsCriteriaEvaluations,
        EvaluatePendingCriteria $evaluatePendingCriteria,
        ConsolidateProductAxisRates $consolidateProductAxisRates,
        IndexProductRates $indexProductRates
    ) {
        $this->productValueInDatabaseDictionarySource = $productValueInDatabaseDictionarySource;
        $this->aspellDictionaryGenerator = $aspellDictionaryGenerator;
        $this->consolidateDashboardRates = $consolidateDashboardRates;
        $this->dashboardRatesProjectionRepository = $dashboardRatesProjectionRepository;
        $this->db = $db;
        $this->createProductsCriteriaEvaluations = $createProductsCriteriaEvaluations;
        $this->evaluatePendingCriteria = $evaluatePendingCriteria;
        $this->consolidateProductAxisRates = $consolidateProductAxisRates;
        $this->indexProductRates = $indexProductRates;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('pimee:data-quality-insights:demo-helper')
            ->setDescription('DO NOT USE IN PRODUCTION - Command to help generate data quality data for several weeks.')
            ->addOption('full-catalog-evaluation', 'f', InputOption::VALUE_NONE, 'Execute synchronous criteria evaluation for all products')
            ->setHidden(true);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Demo helper');

        $io->caution([
            'Only use this command for dev/demo purpose',
            '--',
            'It will generate the dictionary',
            'It will execute synchronous criteria evaluation (for several or all catalog depending of your choice)',
            'It will consolidate the data',
            'It will generate fake consolidation for several days, weeks and month',
            '--',
            'Use with care.'
        ]);

        $confirm = $io->confirm('This command is only for dev / demo purpose. Never use it in production, it will erase data.', false);

        if (false === $confirm) {
            return;
        }

        $now = new ConsolidationDate(new \DateTimeImmutable());

        $io->section('Generate dictionaries');
        $this->aspellDictionaryGenerator->generate($this->productValueInDatabaseDictionarySource);
        $io->success('dictionaries generated');

        if ($input->getOption('full-catalog-evaluation') === true) {
            $io->section('Execute synchronous criteria evaluation for all products');
            $this->fullSynchronousCriteriaEvaluation($io);
            $io->success('full criteria evaluation done');
        } else {
            $io->section('Execute synchronous criteria evaluation for one product of each family');
            $this->partialSynchronousCriteriaEvaluation($io);
            $io->success('partial criteria evaluation done');
        }

        $io->section('Generate fake consolidation');
        $this->consolidateDashboardRates->consolidate($now);

        $statement = $this->db->executeQuery('select type, code, rates from pimee_data_quality_insights_dashboard_rates_projection');

        $results = $statement->fetchAll();

        $idealCatalogRates = [
            'enrichment' => [
                0 => [ 'rank_1' => 65, 'rank_2' => 15, 'rank_3' => 0, 'rank_4' => 5, 'rank_5' => 15 ],
                1 => [ 'rank_1' => 50, 'rank_2' => 15, 'rank_3' => 10, 'rank_4' => 10, 'rank_5' => 15 ],
                2 => [ 'rank_1' => 40, 'rank_2' => 20, 'rank_3' => 15, 'rank_4' => 10, 'rank_5' => 15 ],
                3 => [ 'rank_1' => 30, 'rank_2' => 20, 'rank_3' => 15, 'rank_4' => 15, 'rank_5' => 20 ],
                4 => [ 'rank_1' => 20, 'rank_2' => 20, 'rank_3' => 15, 'rank_4' => 25, 'rank_5' => 25 ],
                5 => [ 'rank_1' => 10, 'rank_2' => 20, 'rank_3' => 20, 'rank_4' => 25, 'rank_5' => 25 ],
                6 => [ 'rank_1' => 10, 'rank_2' => 15, 'rank_3' => 25, 'rank_4' => 25, 'rank_5' => 25 ],
            ],
            'consistency' => [
                0 => [ 'rank_1' => 80, 'rank_2' => 15, 'rank_3' => 0, 'rank_4' => 0, 'rank_5' => 5 ],
                1 => [ 'rank_1' => 13, 'rank_2' => 32, 'rank_3' => 23, 'rank_4' => 22, 'rank_5' => 10 ],
                2 => [ 'rank_1' => 13, 'rank_2' => 32, 'rank_3' => 20, 'rank_4' => 25, 'rank_5' => 10 ],
                3 => [ 'rank_1' => 12, 'rank_2' => 31, 'rank_3' => 26, 'rank_4' => 25, 'rank_5' => 6 ],
                4 => [ 'rank_1' => 12, 'rank_2' => 33, 'rank_3' => 25, 'rank_4' => 23, 'rank_5' => 7 ],
                5 => [ 'rank_1' => 12, 'rank_2' => 33, 'rank_3' => 25, 'rank_4' => 22, 'rank_5' => 8 ],
                6 => [ 'rank_1' => 11, 'rank_2' => 32, 'rank_3' => 25, 'rank_4' => 22, 'rank_5' => 10 ],
            ],
        ];

        $idealFamilyRates = [
            'enrichment' => [
                0 => [ 'rank_1' => 90, 'rank_2' => 10, 'rank_3' => 0, 'rank_4' => 0, 'rank_5' => 0 ],
                1 => [ 'rank_1' => 85, 'rank_2' => 15, 'rank_3' => 0, 'rank_4' => 0, 'rank_5' => 0 ],
                2 => [ 'rank_1' => 70, 'rank_2' => 15, 'rank_3' => 15, 'rank_4' => 0, 'rank_5' => 0 ],
                3 => [ 'rank_1' => 50, 'rank_2' => 15, 'rank_3' => 15, 'rank_4' => 10, 'rank_5' => 10 ],
                4 => [ 'rank_1' => 15, 'rank_2' => 20, 'rank_3' => 20, 'rank_4' => 25, 'rank_5' => 20 ],
                5 => [ 'rank_1' => 10, 'rank_2' => 10, 'rank_3' => 20, 'rank_4' => 30, 'rank_5' => 30 ],
                6 => [ 'rank_1' => 0, 'rank_2' => 0, 'rank_3' => 20, 'rank_4' => 40, 'rank_5' => 40 ],
            ],
            'consistency' => [
                0 => [ 'rank_1' => 90, 'rank_2' => 10, 'rank_3' => 0, 'rank_4' => 0, 'rank_5' => 0 ],
                1 => [ 'rank_1' => 85, 'rank_2' => 15, 'rank_3' => 0, 'rank_4' => 0, 'rank_5' => 0 ],
                2 => [ 'rank_1' => 70, 'rank_2' => 15, 'rank_3' => 15, 'rank_4' => 0, 'rank_5' => 0 ],
                3 => [ 'rank_1' => 50, 'rank_2' => 15, 'rank_3' => 15, 'rank_4' => 10, 'rank_5' => 10 ],
                4 => [ 'rank_1' => 15, 'rank_2' => 20, 'rank_3' => 20, 'rank_4' => 25, 'rank_5' => 20 ],
                5 => [ 'rank_1' => 10, 'rank_2' => 10, 'rank_3' => 20, 'rank_4' => 30, 'rank_5' => 30 ],
                6 => [ 'rank_1' => 0, 'rank_2' => 0, 'rank_3' => 20, 'rank_4' => 40, 'rank_5' => 40 ],
            ],
        ];

        foreach ($results as $result) {
            $rates = json_decode($result['rates'], true);
            $ratesOfTheDay = $rates['daily'][$now->format('Y-m-d')];

            if (empty($ratesOfTheDay)) {
                continue;
            }

            $numberOfProducts = $this->numberOfProducts($ratesOfTheDay);

            $projectionTypeAndCode = ['type' => null, 'code' => null];
            $idealRates = [];

            switch ($result['type']) {
                case 'catalog':
                    $projectionTypeAndCode['type'] = DashboardProjectionType::catalog();
                    $projectionTypeAndCode['code'] = DashboardProjectionCode::catalog();
                    $idealRates =  $idealCatalogRates;
                    break;
                case 'category':
                    $projectionTypeAndCode['type'] = DashboardProjectionType::category();
                    $projectionTypeAndCode['code'] = DashboardProjectionCode::category(new CategoryCode($result['code']));
                    $idealRates =  $idealFamilyRates;
                    break;
                case 'family':
                    $projectionTypeAndCode['type'] = DashboardProjectionType::family();
                    $projectionTypeAndCode['code'] = DashboardProjectionCode::family(new FamilyCode($result['code']));
                    $idealRates =  $idealFamilyRates;
                    break;
            }

            $ratesOfTheDay = $this->generateChaos($ratesOfTheDay, $numberOfProducts, $idealRates, 0);
            $ratesProjections = [];

            for ($i=1; $i < 7; $i++) {
                $ratesProjections[] = new DashboardRatesProjection(
                    $projectionTypeAndCode['type'],
                    $projectionTypeAndCode['code'],
                    $now->modify(sprintf('-%d DAY', $i+1)),
                    new RanksDistributionCollection($this->generateChaos($ratesOfTheDay, $numberOfProducts, $idealRates, $i))
                );
            }

            $ratesProjections[] = new DashboardRatesProjection(
                $projectionTypeAndCode['type'],
                $projectionTypeAndCode['code'],
                $now->modify('sunday last week'),
                new RanksDistributionCollection($ratesOfTheDay)
            );

            for ($i=1; $i < 4; $i++) {
                $ratesProjections[] = new DashboardRatesProjection(
                    $projectionTypeAndCode['type'],
                    $projectionTypeAndCode['code'],
                    $now->modify(sprintf('sunday %d weeks ago', $i)),
                    new RanksDistributionCollection($this->generateChaos($ratesOfTheDay, $numberOfProducts, $idealRates, $i))
                );
            }

            $firstDayThisMonth = $now->modify('first day of this month');
            for ($i=1; $i < 7; $i++) {
                $ratesProjections[] = new DashboardRatesProjection(
                    $projectionTypeAndCode['type'],
                    $projectionTypeAndCode['code'],
                    $firstDayThisMonth->modify(sprintf('last day of %d months ago', $i)),
                    new RanksDistributionCollection($this->generateChaos($ratesOfTheDay, $numberOfProducts, $idealRates, $i))
                );
            }

            $ratesProjections[] = new DashboardRatesProjection(
                $projectionTypeAndCode['type'],
                $projectionTypeAndCode['code'],
                $now->modify('-1 DAY'),
                new RanksDistributionCollection($ratesOfTheDay)
            );

            foreach ($ratesProjections as $ratesProjection) {
                $this->dashboardRatesProjectionRepository->save($ratesProjection);
            }

            $io->writeln(sprintf('    Fake consolidation for <info>%s</info> projection type and <info>%s</info> projection code', $result['type'], $result['code']));
        }

        $io->success('Fake consolidation generated');
    }

    private function generateChaos(array $rates, int $numberOfProducts, array $idealRates, int $day): array
    {
        foreach ($rates as $axe => $scope) {
            foreach ($scope as $scopeCode => $locale) {
                foreach ($locale as $localeCode => $ranks) {
                    foreach ($idealRates[$axe][$day] as $rankCode => $percentage) {
                        $rates[$axe][$scopeCode][$localeCode][$rankCode] = intval(round($numberOfProducts*$percentage/100)) + rand(1, intval(ceil($numberOfProducts*1/100)));
                    }
                }
            }
        }

        return $rates;
    }

    private function numberOfProducts(array $rates): int
    {
        $numberOfProducts = 0;

        foreach ($rates as $axe => $scope) {
            foreach ($scope as $scopeCode => $locale) {
                foreach ($locale as $localeCode => $ranks) {
                    foreach ($ranks as $rankCode => $numberOfProductsEvaluated) {
                        $numberOfProducts += intval($numberOfProductsEvaluated);
                    }

                    return $numberOfProducts;
                }
            }
        }

        return $numberOfProducts;
    }

    private function fullSynchronousCriteriaEvaluation(SymfonyStyle $io): void
    {
        $query = $this->db->executeQuery('select count(*) as nb from pim_catalog_product where product_model_id is null');
        $nbProducts = $query->fetch();

        $nbProducts = intval($nbProducts['nb']);
        if ($nbProducts===0) {
            return;
        }

        $nbSteps = intval(ceil($nbProducts/100));

        $io->comment(sprintf('Launch the evaluation of %d products', $nbProducts));
        $progressBar = new ProgressBar($io, $nbProducts);
        $progressBar->start();

        for ($i = 0; $i<$nbSteps; $i++) {
            $stmt = $this->db->query('select id from pim_catalog_product where product_model_id is null LIMIT ' . $i*100 . ',100');
            $ids = array_map(function ($id) {
                return intval($id);
            }, $stmt->fetchAll(FetchMode::COLUMN, 0));

            $this->evaluateProducts($ids);

            $progressBar->advance(count($ids));
        }

        $progressBar->finish();
    }

    private function partialSynchronousCriteriaEvaluation(SymfonyStyle $io): void
    {
        $stmt = $this->db->query('select max(id) as id from pim_catalog_product where product_model_id is null group by family_id');

        $ids = array_map(function ($id) {
            return intval($id);
        }, $stmt->fetchAll(FetchMode::COLUMN, 0));

        if (count($ids) === 0) {
            $io->error('No products to evaluate');

            return;
        }

        $io->comment(sprintf('Launch the evaluation of %d products', count($ids)));

        $this->evaluateProducts($ids);
    }

    private function evaluateProducts(array $ids): void
    {
        $productIds = array_map(function ($id) {
            return new ProductId($id);
        }, $ids);

        $this->createProductsCriteriaEvaluations->create($productIds);
        $this->evaluatePendingCriteria->execute($ids);
        $this->consolidateProductAxisRates->consolidate($ids);
        $this->indexProductRates->execute($ids);
    }
}
