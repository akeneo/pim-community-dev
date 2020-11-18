<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Enrichment\Performance;

use Blackfire\Bridge\PhpUnit\TestCaseTrait;
use Blackfire\Profile\Configuration;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IndexProductsCommandPerformance extends KernelTestCase
{
    use TestCaseTrait;

    public function test_that_indexing_all_products_with_the_command_is_performant()
    {
        $clientConfiguration = $this->getBlackfireClientConfiguration();
        $clientConfiguration->setEnv('CI');

        $profileConfig = new Configuration();
        $profileConfig->setTitle('Reindex all products');

        // Original value was 32.
        $profileConfig->assert('metrics.sql.queries.count < 40', 'SQL queries');
        // Original value: 15.7s
        $profileConfig->assert('main.wall_time < 20s', 'Total time');
        // Original value: 152MB
        $profileConfig->assert('main.peak_memory < 200mb', 'Memory');
        // Ensure only 3 calls to ES are done - 1 to search, 1 to index products, 1 to index parent product models
        $profileConfig->assert('metrics.http.curl.requests.count == 3', 'Queries to ES');

        $kernel = static::createKernel();
        $application = new Application($kernel);
        $application->setAutoExit(false);
        $command = $application->find('pim:product:index');

        $profile = $this->assertBlackfire($profileConfig, function () use ($command) {
            $command->run(new ArrayInput(['command' => 'pim:product:index', '--all' => true]), new NullOutput());
        });

        echo PHP_EOL . 'Profile complete: ' . $profile->getUrl() . PHP_EOL;
    }
}
