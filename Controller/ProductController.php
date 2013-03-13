<?php
namespace Pim\Bundle\ProductBundle\Controller;

use Symfony\Component\HttpFoundation\File\File;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;

use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pim\Bundle\ProductBundle\Entity\Product;
use Pim\Bundle\ProductBundle\Form\Type\ProductType;

/**
 * Product Controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/product")
 */
class ProductController extends Controller
{

    /**
     * Get product manager
     * @return FlexibleManager
     */
    protected function getProductManager()
    {
        $pm = $this->container->get('product_manager');
        // force data locale if provided
        $dataLocale = $this->getRequest()->get('dataLocale');
        $pm->setLocale($dataLocale);
        // force data scope if provided
        $dataScope = $this->getRequest()->get('dataScope');
        $dataScope = ($dataScope) ? $dataScope : 'ecommerce';
        $pm->setScope($dataScope);

        return $pm;
    }

    /**
     * Get attribute codes
     * @return array
     */
    protected function getAttributeCodesToDisplay()
    {
        return array('name', 'shortDescription', 'size', 'color', 'price');
    }

    /**
     * Index action
     *
     * @param string $dataLocale locale
     * @param string $dataScope  scope
     *
     * @Route("/index/{dataLocale}/{dataScope}", defaults={"dataLocale" = null, "dataScope" = null})
     * @Template()
     *
     * @return array
     */
    public function indexAction($dataLocale, $dataScope)
    {
        $products = $this->getProductManager()->getFlexibleRepository()->findByWithAttributes();

        return array('products' => $products, 'attributes' => $this->getAttributeCodesToDisplay());
    }

    /**
     * Get dedicated PIM filesystem
     *
     * @return Gaufrette\Filesystem
     */
    protected function getPimFS()
    {
        return $this->container->get('knp_gaufrette.filesystem_map')->get('pim');
    }

    /**
     * Create product
     *
     * @param string $dataLocale data locale
     * @param string $dataScope  data scope
     *
     * @Route("/create/{dataLocale}/{dataScope}", defaults={"dataLocale" = null, "dataScope" = null})
     * @Template("PimProductBundle:Product:edit.html.twig")
     *
     * @return array
     */
    public function createAction($dataLocale, $dataScope)
    {
        $entity = $this->getProductManager()->createFlexible(true);

        return $this->editAction($entity, $dataLocale, $dataScope);
    }

    /**
     * Edit product
     *
     * @param Product $entity     product
     * @param string  $dataLocale data locale
     * @param string  $dataScope  data scope
     *
     * @Route(
     *     "/edit/{id}/{dataLocale}/{dataScope}",
     *     requirements={"id"="\d+"},
     *     defaults={"id"=0, "dataLocale" = null, "dataScope" = null}
     * )
     * @Template
     *
     * @return array
     */
    public function editAction(Product $entity, $dataLocale, $dataScope)
    {
        $request = $this->getRequest();

        // create form
        $entClassName = $this->getProductManager()->getFlexibleName();
        $valueClassName = $this->getProductManager()->getFlexibleValueName();
        $form = $this->createForm(new ProductType($entClassName, $valueClassName), $entity);

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                // get uploaded file content
                foreach ($entity->getValues() as $value) {

                    if ($value->getAttribute()->getAttributeType() === AbstractAttributeType::TYPE_FILE_CLASS) {
                        // prepare upload
                        $fileUploaded = $value->fileUpload;
                        $content = file_get_contents($fileUploaded->getPathname());
                        $filename = $entity->getSku() .'-'. $value->getAttribute()->getCode() .'-'. $value->getLocale()
                                    .'-'. $value->getScope() .'-'. time() .'-'. $fileUploaded->getClientOriginalName();

                        // Get Gaufrette Filesystem to write uploaded file content
                        $this->getPimFS()->write($filename, $content);

                        // define name
                        $value->setData($filename);
                    }
                }



                $em = $this->getProductManager()->getStorageManager();
                $em->persist($entity);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', 'Product successfully saved');

                return $this->redirect($this->generateUrl('pim_product_product_index'));
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * Remove product
     *
     * @param Product $entity
     *
     * @Route("/remove/{id}", requirements={"id"="\d+"})
     *
     * @return array
     */
    public function removeAction(Product $entity)
    {
        $em = $this->getProductManager()->getStorageManager();
        $em->remove($entity);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'Product successfully removed');

        return $this->redirect($this->generateUrl('pim_product_product_index'));
    }
}
