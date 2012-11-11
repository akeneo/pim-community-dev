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

        $attribute = $productManager->getNewAttributeInstance();
        $attribute->setCode('size'.time());
        $attribute->setTitle('My title ');
        $attribute->setType(BaseFieldFactory::FIELD_SELECT);
        $attribute->setScope(BaseFieldFactory::SCOPE_GLOBAL);
        $attribute->setUniqueValue(false);
        $attribute->setValueRequired(false);
        $attribute->setSearchable(false);

        // add options
        $values = array('S', 'M', 'L', 'XL');
        foreach ($values as $value) {
            $option = $productManager->getNewAttributeOptionInstance();
            $option->setValue('XXL');
            $attribute->addOption($option);
        }

        $pm = $productManager->getPersistenceManager();
        $pm->persist($attribute);
        $pm->flush();


        var_dump($attribute);

    }
}
