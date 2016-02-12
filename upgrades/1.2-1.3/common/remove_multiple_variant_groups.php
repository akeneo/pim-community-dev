<?php

use Doctrine\ORM\AbstractQuery;
use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

require_once __DIR__.'/../../../app/bootstrap.php.cache';
require_once __DIR__.'/../../../app/AppKernel.php';

const PIM_CATALOG_PRODUCT       = 'pim_catalog_product';
const PIM_CATALOG_GROUP_PRODUCT = 'pim_catalog_group_product';
const PIM_CATALOG_GROUP         = 'pim_catalog_group';
const PIM_CATALOG_GROUP_TYPE    = 'pim_catalog_group_type';

$input = new ArgvInput($argv);
$env = $input->getParameterOption(['-e', '--env']);
if (!$env) {
    echo sprintf("Usage: %s --env=<environment>\nExample: %s --env=dev\n", $argv[0], $argv[0]);
    exit(1);
}

$kernel = new AppKernel($env, $env === 'dev');
$kernel->loadClassCache();
$kernel->boot();

$container = $kernel->getContainer();
$connection = $container->get('doctrine')->getConnection();
$identifierCode = $container->get('pim_catalog.repository.attribute')->getIdentifier()->getCode();
$storageDriver = $container->getParameter('pim_catalog_product_storage_driver');

switch ($storageDriver) {
    case 'doctrine/orm':
        $qb = $container->get('pim_catalog.object_manager.product')
            ->getRepository('Pim\Bundle\CatalogBundle\Model\Product')
            ->createQueryBuilder('p');

        $sql = 'SELECT p.id as product_id ' .
            'FROM %s AS p ' .
            'JOIN %s AS gp ON gp.product_id = p.id ' .
            'JOIN %s AS g ON g.id = gp.group_id ' .
            'JOIN %s AS gt on gt.id = g.type_id AND gt.is_variant = 1 ' .
            'GROUP BY p.id ' .
            'HAVING count(distinct(g.id)) > 1;';

        $stmt = $connection->prepare(sprintf(
            $sql,
            PIM_CATALOG_PRODUCT,
            PIM_CATALOG_GROUP_PRODUCT,
            PIM_CATALOG_GROUP,
            PIM_CATALOG_GROUP_TYPE
        ));

        $stmt->execute();
        $products = $stmt->fetchAll();
        $productIds = [];

        foreach ($products as $product) {
            $productIds[] = $product['product_id'];
        }

        $products = $qb->select('p')
            ->andWhere('p.id IN (:product_ids)')
            ->setParameter(':product_ids', $productIds)
            ->getQuery()
            ->execute();
        break;
    case 'doctrine/mongodb-odm':
        $client = new MongoClient();
        $database = $container->getParameter('mongodb_database');
        $db = $client->$database;
        $productCollection = new MongoCollection($db, PIM_CATALOG_PRODUCT);

        $variantGroupIds = $container->get('pim_catalog.repository.group')
            ->getAllVariantGroupsQB()
            ->select('g.id')
            ->getQuery()
            ->execute(null, AbstractQuery::HYDRATE_ARRAY);

        array_walk($variantGroupIds, function (&$value) {
            $value = $value['id'];
        });

        $selectedProducts = $productCollection->aggregate([
            ['$unwind' => '$groupIds'],
            ['$match'  => [
                'groupIds' => [
                    '$in' => $variantGroupIds
                ]
            ]],
            ['$group'  => [
                '_id'           => '$normalizedData.' . $identifierCode,
                'variant_count' => ['$sum' => 1]
            ]],
            ['$match'  => [
                'variant_count' => ['$gt' => 1]
            ]]
        ])['result'];

        $productIdentifiers = [];
        foreach ($selectedProducts as $product) {
            $productIdentifiers[] = $product['_id'];
        }

        $products = $container->get('pim_catalog.doctrine.query.product_query_factory')
            ->create()
            ->addFilter($identifierCode, 'IN', $productIdentifiers)
            ->execute();
        break;
    default:
        throw new \LogicException(sprintf(
            'Unknown storage driver %s (supported storage drivers : doctrine/orm and doctrine/mongodb-odm)',
            $storageDriver
        ));
}

$output = new ConsoleOutput();

if (count($products) > 0) {
    $output->writeln(sprintf(
        '%s products are in more than one variant group. This is not permitted anymore',
        count($products)
    ));
    $output->writeln('Products in more than one variant group :');

    $lines = [];
    $tableHelper = new TableHelper();
    $tableHelper->setHeaders(['identifier', 'groups']);
    foreach ($products as $product) {
        $line = [];
        $line['identifier'] = (string) $product->getIdentifier();

        $line['groups'] = [];
        foreach ($product->getGroups() as $group) {
            $line['groups'][] = $group->getCode();
        }

        $lines[] = $line;
        $tableHelper->addRow([$line['identifier'], implode(', ', $line['groups'])]);
    }

    $tableHelper->render($output);

    $dialogHelper = new DialogHelper();

    if ($dialogHelper->askConfirmation(
        $output,
        'Would you like to generate a csv file to fix all those products ? (Y,n) '
    )) {
        $tmpFolder = sys_get_temp_dir();
        $filePath = $tmpFolder . '/invalid_product.csv';

        if (!is_writable($tmpFolder)) {
            throw new \Exception(sprintf('The filepath %s is not writable', $filePath));
        }

        $csv = sprintf("%s;groups\n", $identifierCode);
        foreach ($lines as $line) {
            $csv .= sprintf("%s;%s\n", $line['identifier'], implode(',', $line['groups']));
        }

        file_put_contents($filePath, $csv);

        $output->writeln(sprintf('Generated CSV product import : %s', $filePath));
    }
} else {
    $output->writeln('Everything seems fine, no product in multiple variant groups');
}

$output->writeln('DONE !');

$kernel->shutdown();
