<?php

use Symfony\Component\Console\Input\ArgvInput;

require_once __DIR__.'/../../../app/bootstrap.php.cache';
require_once __DIR__.'/../../../app/AppKernel.php';

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

$attributeRepository = $container->get('pim_catalog.repository.attribute');

$attributes = $attributeRepository->findBy(['backendType' => 'prices']);
$attributeCodes = [];
foreach ($attributes as $attribute) {
    $attributeCodes[] = $attribute->getCode();
}

echo "Searching for versions to migrate...\n";

$versionRepository = $container->get('pim_versioning.repository.version');
$qb = $versionRepository->createQueryBuilder('v');

foreach ($attributeCodes as $code) {
    $qb->orWhere(
        $qb->expr()->like('v.snapshot', $qb->expr()->literal('%"'.$code.'"%'))
    );
}

$productClass = $container->getParameter('pim_catalog.entity.product.class');
$qb->andWhere($qb->expr()->eq('v.resourceName', $qb->expr()->literal($productClass)));

$versions = $qb->getQuery()->getResult();

echo sprintf("Migrating %s product versions...\n", count($versions));

$registry = $container->get('pim_catalog.doctrine.smart_manager_registry');
$manager = $registry->getManagerForClass($container->getParameter('pim_versioning.entity.version.class'));

foreach ($versions as $index => $version) {
    $snapshot = $version->getSnapshot();
    foreach ($attributeCodes as $code) {
        unset($snapshot[$code]);
    }
    $version->setSnapshot($snapshot);
    $manager->persist($version);

    if (0 === ($index + 1) % 200) {
        $manager->flush();
    }
}

$manager->flush();

$kernel->shutdown();

echo "Done!\n";
