<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pim\Bundle\CatalogBundle\Model\Product;
use Pim\Bundle\CatalogBundle\Model\ProductSet;

use Pim\Bundle\CatalogBundle\Model\BaseFieldFactory;


class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {

        $productManager = $this->get('pim.catalog.product_manager');

        $field = $productManager->getNewAttributeInstance();
        $field->setCode('size'.time());
        $field->setTitle('My title ');
        $field->setType(BaseFieldFactory::FIELD_SELECT);
        $field->setScope(BaseFieldFactory::SCOPE_GLOBAL);
        $field->setUniqueValue(false);
        $field->setValueRequired(false);
        $field->setSearchable(false);

        // add options
        $values = array('S', 'M', 'L', 'XL');
        foreach ($values as $value) {
            $option = $productManager->getNewAttributeOptionInstance();
            $option->setValue('XXL');
            $field->addOption($option);
        }

        $pm = $productManager->getPersistenceManager();
        $pm->persist($field);
        $pm->flush();


        var_dump($field);

    }
}
