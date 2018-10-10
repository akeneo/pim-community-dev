<?php

require __DIR__.'/vendor/autoload.php';

$kernel = new AppKernel('dev', true);
$kernel->boot();

$query = $kernel->getContainer()->get('pim_catalog.doctrine.query.find_attribute_group_orders_equal_or_superior_to');

$repo = $kernel->getContainer()->get('pim_catalog.repository.attribute_group');
/** @var \Pim\Bundle\CatalogBundle\Entity\AttributeGroup $attributeGroup */
$attributeGroup = $repo->findOneByIdentifier('erp');


$attributeGroup = new \Pim\Bundle\CatalogBundle\Entity\AttributeGroup();
$attributeGroup->setCode('toto');
$attributeGroup->setSortOrder('5');

$ordersEqualsOrSuperior = $query->execute($attributeGroup);

var_dump($ordersEqualsOrSuperior);
if ((int) current($ordersEqualsOrSuperior) === (int) $attributeGroup->getSortOrder()) {
    var_dump('CHANGE TO');
    $rangeOrders = range(min($ordersEqualsOrSuperior), max($ordersEqualsOrSuperior));
    $availableOrders = array_diff($rangeOrders, $ordersEqualsOrSuperior);

    $nextAvailableOrder = current($availableOrders);
    var_dump($nextAvailableOrder);
}
