<?php

namespace Strixos\CatalogEavBundle\Controller;

use Strixos\CatalogEavBundle\Entity\Product;

use Strixos\CatalogEavBundle\Entity\Attribute;

use Strixos\CatalogEavBundle\Entity\ProductType;

use Strixos\CatalogEavBundle\Factory\ProductTypeFactory;

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

        $factory = $this->container->get('strixos_catalog_eav.typefactory');
        $manager = $factory->getObjectManager();

        $attCode = 'name';
        $attribute = new Attribute();
        $attribute->setCode($attCode);
        $manager->persist($attribute);

        $typCode = 'T-shirt';
        $type = new ProductType();
        $type->setCode($typCode);
        $type->addAttribute($attribute);
        $manager->persist($type);

        $product = new Product();
        $product->setType($type);
        $manager->persist($product);

        $manager->flush();

        $name = 'pouet';
        return array('name' => $name);
    }
}
