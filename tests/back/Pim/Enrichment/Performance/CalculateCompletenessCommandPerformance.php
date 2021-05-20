<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Enrichment\Performance;

use Blackfire\Bridge\PhpUnit\TestCaseTrait;
use Blackfire\Profile\Configuration;
use Blackfire\Profile\Metric;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class CalculateCompletenessCommandPerformance extends KernelTestCase
{
    use TestCaseTrait;

    public function test_that_computing_completeness_of_all_products_is_performant()
    {
        $clientConfiguration = $this->getBlackfireClientConfiguration();
        $clientConfiguration->setEnv('CI');

        $profileConfig = new Configuration();
        $profileConfig->setTitle('Recompute completeness of all products');

        $profileConfig->defineMetric(
            new Metric(
                'completeness_calculation',
                '=Akeneo\\Pim\\Enrichment\\Component\\Product\\Completeness\\CompletenessCalculator::fromProductIdentifiers'
            )
        );

        // Original value was 35.
        $profileConfig->assert('metrics.sql.queries.count < 48', 'SQL queries');
        // Original value: 23.1s
        $profileConfig->assert('main.wall_time < 30s', 'Total time');
        // Original value: 174MB
        $profileConfig->assert('main.peak_memory < 200mb', 'Memory');
        // Ensure only 1 completeness calculation is done
        $profileConfig->assert('metrics.completeness_calculation.count == 1', 'Completeness calculation calls');
        // Ensure only 2 calls to ES are performed (1 to search, 1 to index)
        $profileConfig->assert('metrics.http.curl.requests.count == 2', 'Queries to ES');
        // Original value: 4.7s
        $profileConfig->assert('metrics.completeness_calculation.wall_time < 8s', 'Completeness calculation time');

        $kernel = static::createKernel();
        $application = new Application($kernel);
        $application->setAutoExit(false);
        $command = $application->find('pim:completeness:calculate');

        $profile = $this->assertBlackfire($profileConfig, function () use ($command) {
             $command->run(new ArrayInput(['command' => 'pim:completeness:calculate']), new NullOutput());
        });

        echo PHP_EOL . 'Profile complete: ' . $profile->getUrl() . PHP_EOL;
    }
}
