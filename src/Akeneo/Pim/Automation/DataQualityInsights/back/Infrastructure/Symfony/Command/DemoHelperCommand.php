<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Command;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation\ConsolidateDashboardRates;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation\ConsolidateProductScores;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluatePendingCriteria;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\DictionarySource;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\RanksDistributionCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\DashboardRatesProjection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\DashboardScoresProjectionRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ConsolidationDate;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Aspell\AspellDictionaryGenerator;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\UpdateProductsIndex;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DemoHelperCommand extends Command
{
    private DictionarySource $productValueInDatabaseDictionarySource;

    private AspellDictionaryGenerator $aspellDictionaryGenerator;

    private ConsolidateDashboardRates $consolidateDashboardRates;

    private DashboardScoresProjectionRepositoryInterface $dashboardScoresProjectionRepository;

    private Connection $db;

    private CreateCriteriaEvaluations $createProductsCriteriaEvaluations    ;

    private EvaluatePendingCriteria $evaluatePendingCriteria;

    private ConsolidateProductScores $consolidateProductScores;

    private UpdateProductsIndex $updateProductsIndex;

    private CreateCriteriaEvaluations $createProductModelsCriteriaEvaluations;

    private EvaluatePendingCriteria $evaluateProductModelsPendingCriteria;

    public function __construct(
        DictionarySource $productValueInDatabaseDictionarySource,
        AspellDictionaryGenerator $aspellDictionaryGenerator,
        ConsolidateDashboardRates $consolidateDashboardRates,
        DashboardScoresProjectionRepositoryInterface $dashboardScoresProjectionRepository,
        Connection $db,
        CreateCriteriaEvaluations $createProductsCriteriaEvaluations,
        EvaluatePendingCriteria $evaluatePendingCriteria,
        ConsolidateProductScores $consolidateProductScores,
        UpdateProductsIndex $updateProductsIndex,
        CreateCriteriaEvaluations $createProductModelsCriteriaEvaluations,
        EvaluatePendingCriteria $evaluateProductModelsPendingCriteria
    ) {
        parent::__construct();

        $this->productValueInDatabaseDictionarySource = $productValueInDatabaseDictionarySource;
        $this->aspellDictionaryGenerator = $aspellDictionaryGenerator;
        $this->consolidateDashboardRates = $consolidateDashboardRates;
        $this->dashboardScoresProjectionRepository = $dashboardScoresProjectionRepository;
        $this->db = $db;
        $this->createProductsCriteriaEvaluations = $createProductsCriteriaEvaluations;
        $this->evaluatePendingCriteria = $evaluatePendingCriteria;
        $this->consolidateProductScores = $consolidateProductScores;
        $this->updateProductsIndex = $updateProductsIndex;
        $this->createProductModelsCriteriaEvaluations = $createProductModelsCriteriaEvaluations;
        $this->evaluateProductModelsPendingCriteria = $evaluateProductModelsPendingCriteria;
    }

    protected function configure()
    {
        $this
            ->setName('pimee:data-quality-insights:demo-helper')
            ->setDescription('DO NOT USE IN PRODUCTION - Command to help generate data quality data for several weeks.')
            ->addOption('full-catalog-evaluation', 'f', InputOption::VALUE_NONE, 'Execute synchronous criteria evaluation for all products')
            ->setHidden(true);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
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


        $confirm = false;

        if ($input->isInteractive() === true) {
            $confirm = $io->confirm('This command is only for dev / demo purpose. Never use it in production, it will erase data.', false);
        } else {
            $delayInSeconds = 10;
            $io->caution([
                'This command is launched in non-interactive mode, it means that no confirmation will be asked to generate Fake data',
                '',
                'You have ' . $delayInSeconds . ' seconds to stop this process if you don\'t want to loose data.'
            ]);

            $io->createProgressBar($delayInSeconds);
            $io->progressStart();
            for ($i=0; $i<$delayInSeconds; $i++) {
                $io->progressAdvance();
                sleep(1);
            }
            $io->progressFinish();

            $confirm = true;
        }

        if (false === $confirm) {
            return 0;
        }

        $now = new ConsolidationDate(new \DateTimeImmutable());

        $io->section('Generate dictionaries');
        $this->aspellDictionaryGenerator->generate($this->productValueInDatabaseDictionarySource);
        $io->success('dictionaries generated');

        if ($input->getOption('full-catalog-evaluation') === true) {
            $io->section('Execute synchronous criteria evaluation for all products');
            $this->fullSynchronousProductsCriteriaEvaluation($io);
            $io->success('full products criteria evaluation done');

            $io->section('Execute synchronous criteria evaluation for all product models');
            $this->fullSynchronousProductModelsCriteriaEvaluation($io);
            $io->success('full products model criteria evaluation done');
        } else {
            $io->section('Execute synchronous criteria evaluation for one product of each family');
            $this->partialSynchronousCriteriaEvaluation($io);
            $io->success('partial criteria evaluation done');
        }

        $io->section('Generate fake consolidation');
        $this->consolidateDashboardRates->consolidate($now);

        $statement = $this->db->executeQuery('select type, code, scores from pim_data_quality_insights_dashboard_scores_projection');

        $results = $statement->fetchAll();

        $idealCatalogScores = [
            0 => [ 'rank_1' => 80, 'rank_2' => 15, 'rank_3' => 0, 'rank_4' => 0, 'rank_5' => 5 ],
            1 => [ 'rank_1' => 13, 'rank_2' => 32, 'rank_3' => 23, 'rank_4' => 22, 'rank_5' => 10 ],
            2 => [ 'rank_1' => 13, 'rank_2' => 32, 'rank_3' => 20, 'rank_4' => 25, 'rank_5' => 10 ],
            3 => [ 'rank_1' => 12, 'rank_2' => 31, 'rank_3' => 26, 'rank_4' => 25, 'rank_5' => 6 ],
            4 => [ 'rank_1' => 12, 'rank_2' => 33, 'rank_3' => 25, 'rank_4' => 23, 'rank_5' => 7 ],
            5 => [ 'rank_1' => 12, 'rank_2' => 33, 'rank_3' => 25, 'rank_4' => 22, 'rank_5' => 8 ],
            6 => [ 'rank_1' => 11, 'rank_2' => 32, 'rank_3' => 25, 'rank_4' => 22, 'rank_5' => 10 ],
        ];

        $idealFamilyScores = [
            0 => [ 'rank_1' => 90, 'rank_2' => 10, 'rank_3' => 0, 'rank_4' => 0, 'rank_5' => 0 ],
            1 => [ 'rank_1' => 85, 'rank_2' => 15, 'rank_3' => 0, 'rank_4' => 0, 'rank_5' => 0 ],
            2 => [ 'rank_1' => 70, 'rank_2' => 15, 'rank_3' => 15, 'rank_4' => 0, 'rank_5' => 0 ],
            3 => [ 'rank_1' => 50, 'rank_2' => 15, 'rank_3' => 15, 'rank_4' => 10, 'rank_5' => 10 ],
            4 => [ 'rank_1' => 15, 'rank_2' => 20, 'rank_3' => 20, 'rank_4' => 25, 'rank_5' => 20 ],
            5 => [ 'rank_1' => 10, 'rank_2' => 10, 'rank_3' => 20, 'rank_4' => 30, 'rank_5' => 30 ],
            6 => [ 'rank_1' => 0, 'rank_2' => 0, 'rank_3' => 20, 'rank_4' => 40, 'rank_5' => 40 ],
        ];

        foreach ($results as $result) {
            $scores = json_decode($result['scores'], true);
            $scoresOfTheDay = $scores['daily'][$now->format('Y-m-d')];

            if (empty($scoresOfTheDay)) {
                continue;
            }

            $numberOfProducts = $this->numberOfProducts($scoresOfTheDay);

            $projectionTypeAndCode = ['type' => null, 'code' => null];
            $idealScores = [];

            switch ($result['type']) {
                case 'catalog':
                    $projectionTypeAndCode['type'] = DashboardProjectionType::catalog();
                    $projectionTypeAndCode['code'] = DashboardProjectionCode::catalog();
                    $idealScores =  $idealCatalogScores;
                    break;
                case 'category':
                    $projectionTypeAndCode['type'] = DashboardProjectionType::category();
                    $projectionTypeAndCode['code'] = DashboardProjectionCode::category(new CategoryCode($result['code']));
                    $idealScores =  $idealFamilyScores;
                    break;
                case 'family':
                    $projectionTypeAndCode['type'] = DashboardProjectionType::family();
                    $projectionTypeAndCode['code'] = DashboardProjectionCode::family(new FamilyCode($result['code']));
                    $idealScores =  $idealFamilyScores;
                    break;
            }

            $scoresOfTheDay = $this->generateChaos($scoresOfTheDay, $numberOfProducts, $idealScores, 0);
            $ratesProjections = [];

            for ($i=1; $i < 7; $i++) {
                $ratesProjections[] = new DashboardRatesProjection(
                    $projectionTypeAndCode['type'],
                    $projectionTypeAndCode['code'],
                    $now->modify(sprintf('-%d DAY', $i+1)),
                    new RanksDistributionCollection($this->generateChaos($scoresOfTheDay, $numberOfProducts, $idealScores, $i))
                );
            }

            $ratesProjections[] = new DashboardRatesProjection(
                $projectionTypeAndCode['type'],
                $projectionTypeAndCode['code'],
                $now->modify('sunday last week'),
                new RanksDistributionCollection($scoresOfTheDay)
            );

            for ($i=1; $i < 4; $i++) {
                $ratesProjections[] = new DashboardRatesProjection(
                    $projectionTypeAndCode['type'],
                    $projectionTypeAndCode['code'],
                    $now->modify(sprintf('sunday %d weeks ago', $i)),
                    new RanksDistributionCollection($this->generateChaos($scoresOfTheDay, $numberOfProducts, $idealScores, $i))
                );
            }

            $firstDayThisMonth = $now->modify('first day of this month');
            for ($i=1; $i < 7; $i++) {
                $ratesProjections[] = new DashboardRatesProjection(
                    $projectionTypeAndCode['type'],
                    $projectionTypeAndCode['code'],
                    $firstDayThisMonth->modify(sprintf('last day of %d months ago', $i)),
                    new RanksDistributionCollection($this->generateChaos($scoresOfTheDay, $numberOfProducts, $idealScores, $i))
                );
            }

            $ratesProjections[] = new DashboardRatesProjection(
                $projectionTypeAndCode['type'],
                $projectionTypeAndCode['code'],
                $now->modify('-1 DAY'),
                new RanksDistributionCollection($scoresOfTheDay)
            );

            foreach ($ratesProjections as $ratesProjection) {
                $this->dashboardScoresProjectionRepository->save($ratesProjection);
            }

            $io->writeln(sprintf('    Fake consolidation for <info>%s</info> projection type and <info>%s</info> projection code', $result['type'], $result['code']));
        }

        $io->success('Fake consolidation generated');

        return 0;
    }

    private function generateChaos(array $scores, int $numberOfProducts, array $idealRates, int $day): array
    {
        foreach ($scores as $scopeCode => $locale) {
            foreach ($locale as $localeCode => $ranks) {
                foreach ($idealRates[$day] as $rankCode => $percentage) {
                    $scores[$scopeCode][$localeCode][$rankCode] = intval(round($numberOfProducts*$percentage/100)) + rand(1, intval(ceil($numberOfProducts*1/100)));
                }
            }
        }

        return $scores;
    }

    private function numberOfProducts(array $scores): int
    {
        $numberOfProducts = 0;

        foreach ($scores as $scopeCode => $locale) {
            foreach ($locale as $localeCode => $ranks) {
                foreach ($ranks as $rankCode => $numberOfProductsEvaluated) {
                    $numberOfProducts += intval($numberOfProductsEvaluated);
                }

                return $numberOfProducts;
            }
        }

        return $numberOfProducts;
    }

    private function fullSynchronousProductsCriteriaEvaluation(SymfonyStyle $io): void
    {
        $query = $this->db->executeQuery('select count(*) as nb from pim_catalog_product');
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
            $stmt = $this->db->query('select id from pim_catalog_product LIMIT ' . $i*100 . ',100');
            $ids = array_map(function ($id) {
                return intval($id);
            }, $stmt->fetchAll(FetchMode::COLUMN, 0));

            $this->evaluateProducts($ids);

            $progressBar->advance(count($ids));
        }

        $progressBar->finish();
    }

    private function fullSynchronousProductModelsCriteriaEvaluation(SymfonyStyle $io): void
    {
        $query = $this->db->executeQuery('select count(*) as nb from pim_catalog_product_model');
        $nbProducts = $query->fetch();

        $nbProducts = intval($nbProducts['nb']);
        if ($nbProducts===0) {
            return;
        }

        $nbSteps = intval(ceil($nbProducts/100));

        $io->comment(sprintf('Launch the evaluation of %d product models', $nbProducts));
        $progressBar = new ProgressBar($io, $nbProducts);
        $progressBar->start();

        for ($i = 0; $i<$nbSteps; $i++) {
            $stmt = $this->db->query('select id from pim_catalog_product_model LIMIT ' . $i*100 . ',100');
            $ids = array_map(function ($id) {
                return intval($id);
            }, $stmt->fetchAll(FetchMode::COLUMN, 0));

            $this->evaluateProductModels($ids);

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

        $this->createProductsCriteriaEvaluations->createAll($productIds);
        $this->evaluatePendingCriteria->evaluateAllCriteria($ids);
        $this->consolidateProductScores->consolidate($ids);
        $this->updateProductsIndex->execute($ids);
    }

    private function evaluateProductModels(array $ids): void
    {
        $productIds = array_map(function ($id) {
            return new ProductId($id);
        }, $ids);

        $this->createProductModelsCriteriaEvaluations->createAll($productIds);
        $this->evaluateProductModelsPendingCriteria->evaluateAllCriteria($ids);
    }
}
