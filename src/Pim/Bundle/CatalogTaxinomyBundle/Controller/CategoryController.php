<?php
namespace Pim\Bundle\CatalogTaxinomyBundle\Controller;

use Pim\Bundle\CatalogTaxinomyBundle\Helper\JsonTreeHelper;

use Pim\Bundle\CatalogTaxinomyBundle\Entity\CategoryManager;

use Pim\Bundle\CatalogTaxinomyBundle\Entity\Tree;

use Doctrine\ORM\EntityManager;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
/**
 * Category controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/category")
 *
 */
class CategoryController extends Controller
{
    /**
     * Redirect to index action
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function redirectToIndex()
    {
        return $this->redirect($this->generateUrl('pim_catalogtaxinomy_tree_index'));
    }

    /**
     * @return Response
     *
     * @Route("/index")
     * @Template()
     */
    public function indexAction()
    {
        return $this->render('PimCatalogTaxinomyBundle:Category:index.html.twig');
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @Method("GET")
     * @Route("/children")
     * @Template()
     *
     * TODO : Value must be posted ?!
     */
    public function childrenAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            // initialize variables
            $parentId = $request->get('id');

            // Get nodes from parent
            $categories = $this->getManager()->getChildren($parentId);

            // formate in json content
            $data = JsonTreeHelper::childrenResponse($categories);

            return $this->prepareJsonResponse($data);
        } else {
            return $this->redirectToIndex();
        }
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @Route("/search")
     * @Template()
     */
    public function searchAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            // get search data
            $search = $request->get('search_str');

            // find categories by title searching
            $categories = $this->getManager()->search(array('title' => $search));

            // formate in json content
            $data = JsonTreeHelper::searchResponse($categories);

            return $this->prepareJsonResponse($data);
        } else {
            return $this->redirectToIndex();
        }
    }

    /**
     * Create a new node
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Method("POST")
     * @Route("/createNode")
     * @Template()
     */
    public function createNodeAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            // create new object
            $category = $this->getManager()->createNewInstance();
            $category->setTitle($request->get('title'));

            // find parent
            $parent = $this->getManager()->getCategory($request->get('id'));
            $category->setParent($parent);

            // persist object
            $this->getManager()->persist($category);

            // format response to json content
            $data = JsonTreeHelper::createNodeResponse(1, $category->getId());

            return $this->prepareJsonResponse($data);
        } else {
            return $this->redirectToIndex();
        }
    }

    /**
     * Rename a node
     * @param Request $request
     *
     * @return Response
     *
     * @Method("POST")
     * @Route("/renameNode")
     * @Template()
     */
    public function renameNodeAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            // update object
            $this->getManager()->rename($request->get('id'), $request->get('title'));

            // format response to json content
            $data = JsonTreeHelper::statusOKResponse();

            return $this->prepareJsonResponse($data);
        } else {
            return $this->redirectToIndex();
        }
    }

    /**
     * Remove a node
     * @param Request $request
     *
     * @return Response
     *
     * @Method("POST")
     * @Route("/removeNode")
     * @Template()
     */
    public function removeAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            // remove category
            $this->getManager()->removeFromId($request->get('id'));

            // format response to json content
            $data = JsonTreeHelper::statusOKResponse();

            return $this->prepareJsonResponse($data);
        } else {
            return $this->redirectToIndex();
        }
    }

    /**
     * Move a node
     * @param Request $request
     *
     * @return Response
     *
     * @Method("POST")
     * @Route("/moveNode")
     * @Template()
     */
    public function moveNodeAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $categoryId  = $request->get('id');
            $referenceId = $request->get('ref');

            // copy or move category
            if ($request->get('copy') == 1) {
                $this->getManager()->copy($categoryId, $referenceId);
            } else {
                $this->getManager()->move($categoryId, $referenceId);
            }

            // format response to json content
            $data = JsonTreeHelper::statusOKResponse();

            return $this->prepareJsonResponse($data);
        } else {
            return $this->redirectToIndex();
        }
    }

    /**
     * Return a response in json content type with well formated data
     * @param mixed $data
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function prepareJsonResponse($data)
    {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @return CategoryManager
     */
    protected function getManager()
    {
        return $this->get('pim.catalog_taxinomy.category_manager');
    }
}