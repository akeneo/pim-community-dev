<?php

use Pim\Upgrade\SchemaHelper;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;

require_once __DIR__.'/../../../app/bootstrap.php.cache';
require_once __DIR__.'/../../../app/AppKernel.php';

/**
 * Class MigrationProductTemplate
 *
 * @author    Marie Bochu  <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MigrationProductTemplate
{
    /** @var ConsoleOutput */
    protected $output;

    /** @var ContainerInterface  */
    protected $container;

    /** @var string */
    protected $productTemplateTable;

    /**
     * @param ConsoleOutput $output
     * @param ArgvInput     $input
     */
    public function __construct(ConsoleOutput $output, ArgvInput $input)
    {
        $this->output = $output;

        $env = $input->getParameterOption(['-e', '--env']);
        if (!$env) {
            $env = 'dev';
        }

        $this->bootKernel($env);

        $schemaHelper = new SchemaHelper($this->container);
        $this->productTemplateTable = $schemaHelper->getTableOrCollection('product_template');
    }

    /**
     * Execute the product template migration
     */
    public function execute()
    {
        $productTemplates = $this->getProductTemplates();
        if (empty($productTemplates)) {
            $this->output->writeln('<info>There is no product template to update<info>');

            return;
        }

        $this->convert($productTemplates);
        $this->output->writeln('<info>Done !</info>');
    }

    /**
     * @param array $productTemplates
     *
     * @return array
     */
    protected function convert(array $productTemplates)
    {
        $connection = $this->getConnection();

        foreach ($productTemplates as $template) {
            $data = [];
            foreach ($template->getValuesData() as $code => $values) {
                foreach ($values as $i => $value) {
                    if (array_key_exists('value', $value)) {
                        $data[$code][$i] = [
                            'scope'  => $value['scope'],
                            'locale' => $value['locale'],
                            'data'   => $value['value']
                        ];
                    }
                }
            }

            if (!empty($data)) {
                $sql = sprintf("UPDATE %s SET valuesData=:data WHERE id = :id", $this->productTemplateTable);

                $stmt = $connection->prepare($sql);
                $stmt->bindValue('data', json_encode($data));
                $stmt->bindValue('id', $template->getId());
                $stmt->execute();
            }
        }
    }

    /**
     * @return array
     */
    protected function getProductTemplates()
    {
        return $this->container->get('doctrine.orm.default_entity_manager')
            ->getRepository($this->container->getParameter('pim_catalog.entity.product_template.class'))
            ->findAll();
    }

    /**
     * Load kernel
     *
     * @param string $env
     */
    protected function bootKernel($env = 'dev')
    {
        $kernel = new AppKernel($env, $env === 'dev');
        $kernel->loadClassCache();
        $kernel->boot();

        $this->container = $kernel->getContainer();
    }

    /**
     * @return object
     */
    protected function getConnection()
    {
        return $this->container->get('database_connection');
    }
}

$migration = new MigrationProductTemplate(new ConsoleOutput(), new ArgvInput($argv));
$migration->execute();
