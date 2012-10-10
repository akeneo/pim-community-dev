<?php
namespace Strixos\IcecatConnectorBundle\Controller;

use Strixos\IcecatConnectorBundle\Model\BaseExtractor;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use APY\DataGridBundle\Grid\Source\Entity as GridEntity;
use APY\DataGridBundle\Grid\Action\RowAction;

use Strixos\IcecatConnectorBundle\Model\ProductLoader;
use Akeneo\CatalogBundle\Model\BaseFieldFactory;

use \XMLReader;

/**
 *
 * @author    Romain Monceau @ Akeneo
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductController extends Controller
{
    // TODO : put in configuration file
    const URL_PRODUCT = 'https://data.icecat.biz/export/freexml/product_mapping.xml';
    const TMP_FILEPATH_PRODUCTS = '/tmp/product_mapping.xml';
    const AUTH_LOGIN = 'NicolasDupont';
    const AUTH_PASSWORD = '1cec4t**)';


    /**
     * @Route("/product/load-from-icecat")
     * @Template()
     */
    public function loadFromIcecatAction()
    {
        try {
            $em = $this->getDoctrine()->getEntityManager();
            $baseExtractor = new BaseExtractor($em);
            $baseExtractor->extractAndImportProductData();
        } catch (\Exception $e) {
            // TODO display error message
            return array('exception' => $e);
        }

        return $this->redirect($this->generateUrl('strixos_icecatconnector_product_list'));
    }

    /**
     * List Icecat products in a grid
     * @Route("/product/list")
     * @Template()
     */
    public function listAction()
    {
    	// creates simple grid based on entity (ORM)
        $source = new GridEntity('StrixosIcecatConnectorBundle:Product');
        $grid = $this->get('grid');
        $grid->setSource($source);
        // add an action column to load import of all products of a supplier
        $rowAction = new RowAction('Import product to PIM', 'strixos_icecatconnector_product_loadproducts');
        $rowAction->setRouteParameters(array('id'));
        $grid->addRowAction($rowAction);
        // manage the grid redirection, exports response of the controller
        return $grid->getGridResponse('StrixosIcecatConnectorBundle:Product:grid.html.twig');
    }

    /**
     * List Icecat suppliers in a grid
     * @Route("/supplier/load-products/{id}")
     * @Template()
     */
    public function loadProductsAction($id)
    {
        // TODO move this stuff in custom model operation

        // get for supplier = 1 there are lot of data
        //$prodId = 'D9194B';
        $prodId = 'RJ459AV';
        $vendor = 'hp';
        $locale = 'fr';

        // 1) --> load detailled product data
        $loader = new ProductLoader();
        $loader->load($prodId, $vendor, $locale);

        $prodData = $loader->getProductData();
        $prodFeat = $loader->getProductFeatures();

        var_dump($prodData);
        var_dump($prodFeat);

        // 2) --> create type
        $typeCode = $prodData['vendorId'].'-'.$prodData['vendorName'];
        $typeCode.= '-'.$prodData['CategoryId'].'-'.strtolower(str_replace(' ', '', $prodData['CategoryName']));

        // if not exists, create a new type
        $type = $this->container->get('akeneo.catalog.model_producttype');
        $return = $type->find($typeCode);
        if (!$return) {
            $type->create($typeCode);
        }

        // add all fields of prodData as general fields
        $productFieldCodeToValues = array();
        $generalGroupCode = 'General';
        foreach ($prodData as $field => $value) {
            if ($field != 'id') {
                $fieldCode = $prodData['vendorId'].'-'.$prodData['CategoryId'].'-'.$field;
                if (!$type->getField($fieldCode)) {
                    $type->addField($fieldCode, BaseFieldFactory::FIELD_STRING, $generalGroupCode);
                }
                $productFieldCodeToValues[$fieldCode]= $value;
            }
        }

        // create custom group for each features category
        foreach ($prodFeat as $featId => $featData) {
            foreach ($featData as $featName => $fieldData) {
                $groupCode = $featId.'-'.strtolower(str_replace(' ', '', $featName));
                foreach ($fieldData as $fieldName => $value) {
                    $fieldCode = $featId.'-'.strtolower(str_replace(' ', '', $fieldName));
                    if (!$type->getField($fieldCode)) {
                        $type->addField($fieldCode, BaseFieldFactory::FIELD_STRING, $groupCode);
                    }
                    $productFieldCodeToValues[$fieldCode]= $value;
                }
            }
        }

        // save type
        $type->persist();
        $type->flush();

        // 3) ----- create product
        $product = $type->newProductInstance();

        // set product values
        foreach ($productFieldCodeToValues as $fieldCode => $value) {
            $product->setValue($fieldCode, $value);
        }

        // save
        $product->persist();
        $product->flush();

        // TODO: mark as already imported in product table with pim product id so the second time we can load existing
        // product with find an updated it not re-create (as for type)

        return new Response('Load detailled data.');
    }
}