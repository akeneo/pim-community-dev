<?php

namespace Oro\Bundle\TestFrameworkBundle\Tests\Performance;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\Tools\SchemaTool;

class PerformanceTest extends WebTestCase
{

    protected $client;

    public function setUp()
    {
        $this->client = static::createClient(array("debug"=>false));
        $container = $this->client->getContainer();
        $doctrine = $container->get('doctrine');
        $em = $doctrine->getEntityManager();
        $schemaTool = new SchemaTool($em);

        $mdf = $em->getMetadataFactory();
        $classes = $mdf->getAllMetadata();

        list($msec, $sec) = explode(" ", microtime());
        $start=$sec + $msec;

        $schemaTool->dropDatabase();
        $schemaTool->createSchema($classes);


        list($msec, $sec) = explode(" ", microtime());
        $stop=$sec + $msec;
        echo "\nDropping and creating schema time is " . round($stop - $start, 4) . " sec\n";
        ob_flush();
    }

    /**
     * @param int $counter
     * @dataProvider dataSearchLoad
     */
    public function testSearchLoad($counter = 1)
    {
        //Load all fixtures
        $kernel = $this->client->getKernel();
        $container = $this->client->getContainer();
        $container->counter = $counter;

        $application = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
        $application->setAutoExit(false);
        $options = array('command' => 'oro:search:create-index');
        $options['--env'] = "test";
        $application->run(new \Symfony\Component\Console\Input\ArrayInput($options));

        $options = array('command' => 'doctrine:fixtures:load');
        $options['--fixtures'] = __DIR__ . DIRECTORY_SEPARATOR . "Fixtures";
        $options['--env'] = "test";
        $options['--no-interaction'] = null;
        $options['--no-debug'] = null;
        list($msec, $sec) = explode(" ", microtime());
        $start = $sec + $msec;

        $application->run(new \Symfony\Component\Console\Input\ArrayInput($options));

        list($msec, $sec) = explode(" ", microtime());
        $stop = $sec + $msec;
        $counter = $counter * 3;
        echo "\nUploading execution time of {$counter} entities is " . round($stop - $start, 4) . " sec";
    }

    public function dataSearchLoad()
    {
        return array(
            '999' => array('999' => 333),
            '3000' => array('2997' => 1000),
            '6000' => array('5997' => 2000),
            '9000' => array('9999' => 3000)
        );
    }

    protected function tearDown()
    {
        unset($this->client);
    }
}
