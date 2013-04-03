<?php
namespace Pim\Bundle\ProductBundle\Controller;

use Pim\Bundle\ProductBundle\Manager\MediaManager;

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
     * @return MediaManager
     */
    protected function getMediaManager()
    {
        return $this->container->get('pim_media_manager');
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
     *     "{id}/edit",
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
                $index = 0;
                // upload files if exist
                foreach ($entity->getValues() as $value) {
                    if ($value->getMedia() !== null) {
                        // upload file
                        if ($value->getMedia()->getFile() !== null) {
                            $filename = $entity->getSku() .'-'. $value->getAttribute()->getCode() .'-'.
                                        $value->getLocale() .'-'. $value->getScope() .'-'. time() .'-'.
                                        $value->getMedia()->getFile()->getClientOriginalName();

                            $this->getMediaManager()->upload($value->getMedia(), $filename);
                        } elseif ($value->getMedia()->getFile() === null &&
                                (!$value->getMedia()->getId() ||
                                $form->get('values')->get($index)->get('media')->get('remove')->getData() === true)) {
                            // unkink media if exists
                            if ($this->getMediaManager()->fileExists($value->getMedia())) {
                                $this->getMediaManager()->delete($value->getMedia());
                            }
                            // remove value if empty file
                            $value->setMedia(null);
                        }
                    }
                    $index++;
                }

                $em = $this->getProductManager()->getStorageManager();
                $em->persist($entity);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', 'Product successfully saved');
                $params = array('id' => $entity->getId(), 'dataLocale' => $dataLocale, 'dataScope' => $dataScope);

                return $this->redirect($this->generateUrl('pim_product_product_edit', $params));
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
