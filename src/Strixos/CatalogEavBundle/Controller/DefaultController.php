<?php

namespace Strixos\CatalogEavBundle\Controller;

use Strixos\CatalogEavBundle\Entity\Group;
use Strixos\CatalogEavBundle\Entity\Type;
use Strixos\CatalogEavBundle\Entity\Value;
use Strixos\CatalogEavBundle\Entity\Field;
use Strixos\CatalogEavBundle\Entity\Product;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
        // TODO take a look on datafixtures to test
/*
        // use factory to build entity and manager to persist
        $factory = $this->container->get('strixos_catalog_eav.productfactory');
        $manager = $this->container->get('doctrine')->getEntityManager();

        // create fields
        $fieldCodes = array('sku', 'name', 'hddsize', 'cpu');
        $fields = array();
        foreach ($fieldCodes as $fieldCode) {
            $field = $factory->buildField($fieldCode);
            var_dump($field);
            $fields[]= $field;
            $manager->persist($field);
        }
        // create type
        $type = $factory->buildType('computer');
        foreach ($fields as $field) {
            $type->addField($field);
        }
        var_dump($type);
        $manager->persist($type);
        // create group
        $group = $factory->buildGroup('General', $type);
        foreach ($fields as $field) {
            $group->addField($field);
        }
        $type->addGroup($group);
        var_dump($group);
        var_dump($type);
        $manager->persist($group);
        // create product
        $product = $factory->buildEntity($type);
        $product->setName('Dell Xps 15z');
        $product->setSku('dell-xps-15z');
        $product->setHddsize('200 GO SATA');
        $product->setCpu('Core I7');
        $manager->persist($product);

        // save data
        $manager->flush();
*/

/*
        exit();

        $manager = $this->getDoctrine()->getEntityManager();

        $fieldCode = 'name';
        if (!$fieldName = $manager->getRepository('StrixosCatalogEavBundle:Field')
                ->findOneBy(array('code' => $fieldCode))) {
            $fieldName = new Field();
            $fieldName->setCode($fieldCode);
            $manager->persist($fieldName);
        }

        $fieldCode = 'sku';
        if (!$fieldSku = $manager->getRepository('StrixosCatalogEavBundle:Field')
                ->findOneBy(array('code' => $fieldCode))) {
            $fieldSku = new Field();
            $fieldSku->setCode($fieldCode);
            $manager->persist($fieldSku);
        }


        $typCode = 'T-shirt';
        if (!$type = $manager->getRepository('StrixosCatalogEavBundle:Type')
                ->findOneBy(array('code' => $typCode))) {
            $type = new Type();
            $type->setCode($typCode);
            $type->addField($fieldName);
            $type->addField($fieldSku);
            $manager->persist($type);
        }

        $groupCode = 'Informations';
        if (!$group = $manager->getRepository('StrixosCatalogEavBundle:Group')
                ->findOneBy(array('code' => $groupCode))) {
            $group = new Group();
            $group->setCode($groupCode);
            $group->setType($type);
            $group->addField($fieldName);
            $group->addField($fieldSku);
            $manager->persist($group);
        }

        $product = new Product();
        $product->setType($type);
        $manager->persist($product);
        $manager->flush();


        // get existing product
        $product = $manager->getRepository('StrixosCatalogEavBundle:Product')
            ->findOneById(1);

        $product->setSku('yellow');
        echo 'sku: '. $product->getSku();
        echo '<hr />';
*/
        $name = 'pouet';
        return array('name' => $name);
    }
}
