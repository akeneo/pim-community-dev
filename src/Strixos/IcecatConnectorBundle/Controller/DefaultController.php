<?php

namespace Strixos\IcecatConnectorBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use Strixos\IcecatConnectorBundle\Model\SupplierLoader;

use Strixos\IcecatConnectorBundle\Model\ProductLoader;
use Strixos\IcecatConnectorBundle\Model\BaseExtractor;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * Display list of suppliers
     *
     * @Route("/default/index")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        // count all
        $query = $em->createQuery(
            'SELECT count(distinct s.id) as nbSymbols, count(distinct s.supplierId) as nbSuppliers,
            count(distinct s.distributorId) as nbDistributors
            FROM StrixosIcecatConnectorBundle:Supplier as s'
        );
        $result = $query->getSingleResult();
        $nbSuppliers    = $result['nbSuppliers'];
        $nbSymbols      = $result['nbSymbols'];
        $nbDistributors = $result['nbDistributors'];
        // count products
        $query = $em->createQuery(
            'SELECT count(distinct p.id) as nbProducts
            FROM StrixosIcecatConnectorBundle:Product as p'
        );
        $result = $query->getSingleResult();
        $nbProducts     = $result['nbProducts'];
        // get first 100 suppliers ordered by nb products TODO: change column type and add index before
        /*$query = $em->createQuery(
            'SELECT s, count(distinct p.id) as nbProducts
            FROM StrixosIcecatConnectorBundle:Supplier as s
            INNER JOIN StrixosIcecatConnectorBundle:Product as p ON (s.supplier_id = p.supplier_id)
            GROUP BY p.supplier_id
            ORDER BY nbProducts desc
            LIMIT 100'
        );*/
        $query = $em->createQuery('
            SELECT s
            FROM StrixosIcecatConnectorBundle:Supplier as s'
        );
        $list = $query->getResult();

        return $this->render(
            'StrixosIcecatConnectorBundle:Supplier:index.html.twig', array(
                'list'           => $list,
                'nbSuppliers'    => $nbSuppliers,
                'nbSymbols'      => $nbSymbols,
                'nbDistributors' => $nbDistributors,
                'nbProducts'     => $nbProducts
            )
        );
        /*
        $prodId = 'RJ459AV';
        $vendor = 'hp';
        $locale = 'fr';

        $loader = new ProductLoader();
        $loader->load($prodId, $vendor, $locale);

*/


        return array('name' => 'toto');
    }

    /**
     * Importing base data from open icecat
    * @Route("/default/setup")
    * @Template()
    */
    public function setupAction()
    {
        // TODO replace by injection and use loader as services ?
        $entityManager = $this->getDoctrine()->getEntityManager();
        $extractor = new BaseExtractor($entityManager);
        $extractor->process();
        return new Response('Base data (suppliers and products) have been retrieved from Open Icecat.');
    }

    /**
    * Load detailled product data from open icecat
    * @Route("/default/load")
    * @Template()
    */
    public function loadAction()
    {
        // get for supplier = 1 there are lot of data
        $prodId = 'D9194B';
        $prodId = 'RJ459AV';

        $vendor = 'hp';
        $locale = 'fr';

        // --> load data
        $loader = new ProductLoader();
        $loader->load($prodId, $vendor, $locale);

        $prodData = $loader->getProductData();
        $prodFeat = $loader->getProductFeatures();

        var_dump($prodData);
        var_dump($prodFeat);

        // --> create /update product

        $factory = $this->container->get('strixos_catalog_eav.productfactory');
        $manager = $this->container->get('doctrine')->getEntityManager();

        // get / create type
        $typeCode = $prodData['vendorId'].'-'.$prodData['vendorName'];
        $typeCode.= '-'.$prodData['CategoryId'].'-'.$prodData['CategoryName'];
        $type = $manager->getRepository('StrixosCatalogEavBundle:Type')->findOneByCode($typeCode);
        if (!$type) {
            $type = $factory->buildType($typeCode);
        }

        // create product
        $product = $manager->getRepository('StrixosCatalogEavBundle:Type')->findOneByCode($typeCode);
        if (!$product) {
            $product = $factory->buildEntity($type);
        }

        // get / create groups and get / create field
        $fieldCodeToValue = array();
        foreach ($prodFeat as $groupInd => $groupData) {
            foreach ($groupData as $groupCode => $fieldData) {
                // get group
                $group = $manager->getRepository('StrixosCatalogEavBundle:Group')
                    ->findOneBy(array('code' => $groupCode, 'type' => $type->getId()));
                if (!$group) {
                    $group = $factory->buildGroup($groupCode, $type);
                    $type->addGroup($group);
                }
                // get fields
                foreach ($fieldData as $fieldCode => $fieldValue) {
                    // get field
                    $field = $manager->getRepository('StrixosCatalogEavBundle:Field')
                        ->findOneByCode($fieldCode);
                    if (!$field) {
                        $field = $factory->buildField($fieldCode);
                        $manager->persist($field);
                        // add attribute to type
                        $type->addField($field);
                        // add attribute to group
                        $group->addField($field);
                    }

                    // TODO set product values

                }
                $manager->persist($group);
            }
        }
        $manager->persist($type);

        //$manager->persist($product);
        var_dump($product);

        $manager->flush();

        return new Response('Load detailled data.');
    }

}
