<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Command;


use Akeneo\Pim\Automation\DataQualityInsights\Application\ConsolidateDashboardRates;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\DictionarySource;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\DashboardRatesProjection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ConsolidationDate;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker\AspellDictionaryGenerator;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Install\InitializeCriteriaEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\DashboardRatesProjectionRepository;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DemoHelperCommand extends Command
{
    /** @var DictionarySource */
    private $productValueInDatabaseDictionarySource;

    /** @var AspellDictionaryGenerator */
    private $aspellDictionaryGenerator;

    /** @var InitializeCriteriaEvaluation */
    private $initializeCriteriaEvaluation;

    /** @var ConsolidateDashboardRates */
    private $consolidateDashboardRates;

    /** @var DashboardRatesProjectionRepository */
    private $dashboardRatesProjectionRepository;

    /** @var Connection */
    private $db;

    public function __construct(
        DictionarySource $productValueInDatabaseDictionarySource,
        AspellDictionaryGenerator $aspellDictionaryGenerator,
        InitializeCriteriaEvaluation $initializeCriteriaEvaluation,
        ConsolidateDashboardRates $consolidateDashboardRates,
        DashboardRatesProjectionRepository $dashboardRatesProjectionRepository,
        Connection $db
    )
    {
        $this->productValueInDatabaseDictionarySource = $productValueInDatabaseDictionarySource;
        $this->aspellDictionaryGenerator = $aspellDictionaryGenerator;
        $this->initializeCriteriaEvaluation = $initializeCriteriaEvaluation;
        $this->consolidateDashboardRates = $consolidateDashboardRates;
        $this->dashboardRatesProjectionRepository = $dashboardRatesProjectionRepository;
        $this->db = $db;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('pimee:data-quality-insights:demo-helper')
            ->setDescription('DO NOT USE IN PRODUCTION - Command to help generate data quality data for several weeks.')
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
            'It will initialize the evaluation of criteria',
            'It will consolidate the data',
            'It will generate fake consolidation for several days, weeks and month',
            '--',
            'Use with care.'
        ]);

        $confirm = $io->confirm('This command is only for dev / demo purpose. Never use it in production, it will erase data.', false);

        if (false === $confirm) {
            return;
        }

        $now = new \DateTimeImmutable();

        $io->section('Generate dictionaries');
        $this->aspellDictionaryGenerator->generate($this->productValueInDatabaseDictionarySource);
        $io->success('dictionaries generated');
        $io->section('Initialize criteria evaluation for all products');
        $this->initializeCriteriaEvaluation->initialize();
        $io->success('criteria evaluation done');
        $io->section('Generate fake consolidation');
        $this->consolidateDashboardRates->consolidate(new ConsolidationDate($now));

        $statement = $this->db->executeQuery('select type, code, rates from pimee_data_quality_insights_dashboard_rates_projection');

        $results = $statement->fetchAll();

        foreach($results as $result)
        {
            $rates = json_decode($result['rates'], true);
            $ratesOfTheDay = $rates['daily'][$now->format('Y-m-d')];

            if(empty($ratesOfTheDay))
            {
                continue;
            }

            $numberOfProducts = $this->numberOfProducts($ratesOfTheDay);

            $idealRates = [
                0 => [
                    'rank_1' => 15,
                    'rank_2' => 30,
                    'rank_3' => 25,
                    'rank_4' => 20,
                    'rank_5' => 10
                ],
                1 => [
                    'rank_1' => 13,
                    'rank_2' => 32,
                    'rank_3' => 23,
                    'rank_4' => 22,
                    'rank_5' => 10
                ],
                2 => [
                    'rank_1' => 13,
                    'rank_2' => 32,
                    'rank_3' => 20,
                    'rank_4' => 25,
                    'rank_5' => 10
                ],
                3 => [
                    'rank_1' => 12,
                    'rank_2' => 31,
                    'rank_3' => 26,
                    'rank_4' => 25,
                    'rank_5' => 6
                ],
                4 => [
                    'rank_1' => 12,
                    'rank_2' => 33,
                    'rank_3' => 25,
                    'rank_4' => 23,
                    'rank_5' => 7
                ],
                5 => [
                    'rank_1' => 12,
                    'rank_2' => 33,
                    'rank_3' => 25,
                    'rank_4' => 22,
                    'rank_5' => 8
                ],
                6 => [
                    'rank_1' => 11,
                    'rank_2' => 32,
                    'rank_3' => 25,
                    'rank_4' => 22,
                    'rank_5' => 10
                ],
                7 => [
                    'rank_1' => 8,
                    'rank_2' => 12,
                    'rank_3' => 40,
                    'rank_4' => 12,
                    'rank_5' => 20
                ]

            ];

            $ratesOfTheDay = $this->generateChaos($ratesOfTheDay, $numberOfProducts, $idealRates[0]);

            $rates['daily'][$now->modify('-1 DAY')->format('Y-m-d')] = $ratesOfTheDay;

            for($i=2; $i < 8; $i++)
            {
                $rates['daily'][$now->modify(sprintf('-%d DAY', $i))->format('Y-m-d')] = $this->generateChaos($ratesOfTheDay, $numberOfProducts, $idealRates[$i]);
            }

            $rates['weekly'][$now->modify('-1 WEEK')->format('Y-W')] = $ratesOfTheDay;

            for($i=2; $i < 5; $i++)
            {
                $rates['weekly'][$now->modify(sprintf('-%d WEEK', $i))->format('Y-W')] = $this->generateChaos($ratesOfTheDay, $numberOfProducts, $idealRates[$i]);
            }

            $rates['monthly'][$now->modify('-1 MONTH')->format('Y-m')] = $ratesOfTheDay;

            for($i=2; $i < 7; $i++)
            {
                $rates['monthly'][$now->modify(sprintf('-%d MONTH', $i))->format('Y-m')] = $this->generateChaos($ratesOfTheDay, $numberOfProducts, $idealRates[$i]);
            }

            $projectionTypeAndCode = ['type' => null, 'code' => null];

            switch($result['type']){
                case 'catalog':
                    $projectionTypeAndCode['type'] = DashboardProjectionType::catalog();
                    $projectionTypeAndCode['code'] = DashboardProjectionCode::catalog();
                    break;
                case 'category':
                    $projectionTypeAndCode['type'] = DashboardProjectionType::category();
                    $projectionTypeAndCode['code'] = DashboardProjectionCode::category(new CategoryCode($result['code']));
                    break;
                case 'family':
                    $projectionTypeAndCode['type'] = DashboardProjectionType::family();
                    $projectionTypeAndCode['code'] = DashboardProjectionCode::family(new FamilyCode($result['code']));
                    break;
            }

            $this->dashboardRatesProjectionRepository->save(
                new DashboardRatesProjection(
                    $projectionTypeAndCode['type'],
                    $projectionTypeAndCode['code'],
                    $rates
                )
            );

            $io->writeln(sprintf('    Fake consolidation for <info>%s</info> projection type and <info>%s</info> projection code',$result['type'], $result['code']));

        }

        $io->success('Fake consolidation generated');
    }

    private function generateChaos(array $rates, int $numberOfProducts, array $idealRates): array
    {
        foreach($rates as $axe => $scope)
        {
            foreach($scope as $scopeCode => $locale)
            {
                foreach($locale as $localeCode => $ranks)
                {
                    foreach($idealRates as $rankCode => $percentage)
                    {
                        $rates[$axe][$scopeCode][$localeCode][$rankCode] = ($numberOfProducts*$percentage/100) + (rand(1, intval(ceil($numberOfProducts*5/100))));
                    }
                }
            }
        }

        return $rates;
    }

    private function numberOfProducts(array $rates): int
    {
        $numberOfProducts = 0;

        foreach($rates as $axe => $scope)
        {
            foreach($scope as $scopeCode => $locale)
            {
                foreach($locale as $localeCode => $ranks)
                {
                    foreach($ranks as $rankCode => $numberOfProductsEvaluated)
                    {
                        $numberOfProducts += intval($numberOfProductsEvaluated);
                    }

                    return $numberOfProducts;
                }
            }
        }

        return $numberOfProducts;
    }
}
