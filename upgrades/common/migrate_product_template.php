<?php

use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

require_once __DIR__.'/../../app/bootstrap.php.cache';
require_once __DIR__.'/../../app/AppKernel.php';

class MigrationProductTemplate
{
    const PIM_CATALOG_PRODUCT_TEMPLATE = 'pim_catalog_product_template';

    protected $output;
    protected $env;
    protected $container;
    protected $kernel;

    public function __construct(ConsoleOutput $output, ArgvInput $input)
    {
        $this->output = $output;

        $env = $input->getParameterOption(['-e', '--env']);
        if (!$env) {
            $env = 'dev';
        }

        $this->kernel($env);
    }

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
                $sql = sprintf("UPDATE %s SET valuesData=:data WHERE id = :id", self::PIM_CATALOG_PRODUCT_TEMPLATE);

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
    public function kernel($env = 'dev')
    {
        $this->kernel = new AppKernel($env, $env === 'dev');
        $this->kernel->loadClassCache();
        $this->kernel->boot();

        $this->container = $this->kernel->getContainer();
    }

    protected function getConnection()
    {
        return $this->container->get('database_connection');
    }
}

$migration = new MigrationProductTemplate(new ConsoleOutput(), new ArgvInput($argv));
$migration->execute();
