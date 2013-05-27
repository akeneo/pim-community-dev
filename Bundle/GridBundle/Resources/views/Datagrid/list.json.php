<?php

$data = array();

/** @var $datagrid \Oro\Bundle\GridBundle\Datagrid\DatagridView */
/** @var $datagridObject \Oro\Bundle\GridBundle\Datagrid\Datagrid */
$datagridObject = $datagrid->getDatagrid();

/** @var $view \Symfony\Bundle\FrameworkBundle\Templating\PhpEngine */
/** @var $twigEnvironment \Twig_Environment */
$twigEnvironment = $view->container->get('twig');

foreach ($datagridObject->getResults() as $object) {
    $record = array();
    /** @var $property \Oro\Bundle\GridBundle\Property\PropertyInterface */
    foreach ($datagridObject->getProperties() as $property) {
        if ($property instanceof \Oro\Bundle\GridBundle\Property\TwigPropertyInterface) {
            /** @var $property \Oro\Bundle\GridBundle\Property\TwigPropertyInterface */
            $property->setEnvironment($twigEnvironment);
        }
        $record[$property->getName()] = $property->getValue($object);
    }
    $data[] = $record;
}

$result = array(
    'data' => $data,
    'options' => array(
        'totalRecords' => $datagridObject->getPager()->getNbResults()
    ),
);

echo json_encode($result);
