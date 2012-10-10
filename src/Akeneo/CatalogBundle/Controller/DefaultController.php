<?php

namespace Akeneo\CatalogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Akeneo\CatalogBundle\Model\Product;
use Akeneo\CatalogBundle\Model\ProductType;

use Akeneo\CatalogBundle\Document\ProductTypeMongo;
use Akeneo\CatalogBundle\Document\ProductFieldMongo;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {

/*
        $type = new ProductTypeMongo();
        var_dump($type);

        $field = new ProductFieldMongo();
        $field->setCode('sku');
        $type->addField($field);

        var_dump($type);

        $dm = $this->get('doctrine.odm.mongodb')->getManager();
        $dm->persist($type);
        $dm->flush();

        var_dump($type);
*/

        // create type
        $ind = time();
        $typeCode = 'tshirt'.$ind;
        $type = $this->container->get('akeneo.catalog.model_producttype_mongo');
        $type = $type->create($typeCode);
        $type->addField('sku', 'text', 'General');
        $type->addField('name', 'text', 'General');
        $type->addField('color', 'text', 'Technical');
        // persist
        $type->persist();
        $type->flush();


        $product = $type->newProductInstance();
        $product->setValue('sku', 'My sku');
        $product->setValue('name', 'My nom');
        $product->setValue('color', 'Red');

        $product->switchLocale('fr_FR');
        $product->setValue('color', 'Rouge');

        // persist
        $product->persist();
        $product->flush();

        echo 'done';



        exit();

        /*
        $type = $this->container->get('akeneo.catalog.model_producttype');
        $type = $type->find('base');

        $product = $this->container->get('akeneo.catalog.model_product_mongo');
        $product->create('pouetpoue');
        $product->setColor('Red');
        $product->setName('my name');

        var_dump($product->getObject());

        $om = $product->getManager();
        $om->persist($product->getObject());
        $om->flush();

        var_dump($product->getObject());
*/
        exit();




        var_dump($product);

        die('data');
/*
        $article = $em->find('Entity\Article', 1);
        $article->setLocale('ru_ru');
        $em->refresh($article);
        exit();
*/

//        $ind = time();

/*
        // create type
        $typeCode = 'tshirt'.$ind;
        //$type = new ProductType($manager);
        $type = $this->container->get('akeneo.catalog.model_producttype');
        $type = $type->create($typeCode);
        $type->addField('sku', Field::TYPE_TEXT, 'General');
        $type->addField('name', Field::TYPE_TEXT, 'General');
        $type->addField('color', Field::TYPE_TEXT, 'Technical');
        // persist
        $type->persist();
        $type->flush();

        // create product
        $product = $type->newProductInstance();
        // set values
        $product->setValue('sku', 'My sku');
        $product->setName('My name');
        $product->setColor('Pink');
        // persist
        $product->persist();
        $product->flush();

        // translate product
        $product->setLocale('fr_FR');
        $product->setName('Mon nom');
        // persist
        $product->persist();
        $product->flush();
*/
/*
        $manager = $this->getDoctrine()->getEntityManager();

        echo '-----------<br/>';
        $value = $manager->getRepository('Akeneo\CatalogBundle\Entity\Product\Value')->find(21);
        echo $value->getData().'<br/>';
        $value->setTranslatableLocale('fr_FR');
        $manager->refresh($value);
        echo $value->getData().'<br/>';

        echo '-----------<br/>';
        $value = $manager->getRepository('Akeneo\CatalogBundle\Entity\Product\Value')->find(22);
        echo $value->getData().'<br/>';
        $value->setTranslatableLocale('fr_FR');
        $manager->refresh($value);
        echo $value->getData().'<br/>';

        echo '-----------<br/>';


        echo '-----------<br/>';
        $product = $manager->getRepository('Akeneo\CatalogBundle\Entity\Product\Entity')->find(7);
        foreach ($product->getValues() as $value) {
            $value->setTranslatableLocale(null);
            $manager->refresh($value);
            echo $value->getData().'<br/>';
        }

        echo '-----------<br/>';
        foreach ($product->getValues() as $value) {
            $value->setTranslatableLocale('fr_FR');
            $manager->refresh($value);
            echo $value->getData().'<br/>';
        }

        echo '-----------<br/>';
        */

        $product = $this->container->get('akeneo.catalog.model_product');
        $product->find(2);
        foreach ($product->getFieldsCodes() as $fieldCode) {
            var_dump($product->getValue($fieldCode));
        }
        echo '-----------<br/>';

        $product->switchLocale('fr_FR');
        foreach ($product->getFieldsCodes() as $fieldCode) {
            var_dump($product->getValue($fieldCode));
        }
        echo '-----------<br/>';

        $name = 'Test';
        return array('name' => $name);
    }
}
