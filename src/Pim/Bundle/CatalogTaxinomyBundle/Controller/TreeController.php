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
 * Enter description here ...
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/tree")
 *
 */
class TreeController extends Controller
{
    /**
     * @return Response
     *
     * @Route("/index")
     * @Template()
     */
    public function indexAction()
    {
        $res = $this->getManager()->getCategories();

        return $this->render(
            'PimCatalogTaxinomyBundle:Tree:index.html.twig',
            array('tree' => $repo->childrenHierarchy())
        );
    }

    /**
     * @return Response
     *
     * @Route("/tree")
     * @Template()
     */
    public function treeAction()
    {
        return $this->render('PimCatalogTaxinomyBundle:Tree:tree.html.twig');
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @Route("/children")
     * @Template()
     *
     * TODO : Must be XmlHttpRequest
     * TODO : Value must be posted ?!
     *
     * TODO : must return with 1 parameter
     * [{"attr":{"id":"node_2","rel":"drive"},"data":"C:","state":"closed"},{"attr":{"id":"node_6","rel":"drive"},"data":"D:","state":""}]
     */
    public function childrenAction(Request $request)
    {
        // initialize variables
        $parentId = $request->get('id');
        $recursive = false;

        // Get nodes from parent
        $categories = $this->getManager()->getChildren($parentId);

        // formate in json content
        $data = JsonTreeHelper::childrenResponse($categories);

        return $this->prepareJsonResponse($data);
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
        // get search data
        $search = $request->get('search');

        // find categories by title searching
        $categories = $this->getManager()->search(array('title' => $search));
        echo count($categories);

        // formate in json content
        $data = JsonTreeHelper::searchResponse($categories);

        return $this->prepareJsonResponse($data);
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
     *
     * TODO : LEFT / RIGHT will be implemented with Category feature
     */
    public function createNodeAction(Request $request)
    {
        // create new object
        $category = $this->getManager()->createNewInstance();
        $category->setTitle($request->get('title'));
        $category->setType($request->get('type'));

        // find parent
        $parent = $this->getManager()->getCategory($request->get('id'));
        $category->setParent($parent);

        // persist object
        $this->getManager()->persist($category);

        // format response to json content
        $data = JsonTreeHelper::createNodeResponse(1, $category->getId());

        return $this->prepareJsonResponse($data);
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
        // update object
        $this->getManager()->rename($request->get('id'), $request->get('title'));

        // format response to json content
        $data = JsonTreeHelper::statusOKResponse();

        return $this->prepareJsonResponse($data);
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
        // remove category
        $this->getManager()->removeFromId($request->get('id'));

        // format response to json content
        $data = JsonTreeHelper::statusOKResponse();

        return $this->prepareJsonResponse($data);
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
    }

    /**
     * @return CategoryManager
     */
    protected function getManager()
    {
        return $this->get('pim.catalog_taxinomy.category_manager');
    }
}