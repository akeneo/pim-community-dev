<?php
namespace Pim\Bundle\CatalogTaxinomyBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use Doctrine\ORM\EntityManager;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;

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
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->get('doctrine.orm.entity_manager');
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository()
    {
        return $this->getEntityManager()
                    ->getRepository('Pim\Bundle\CatalogTaxinomyBundle\Entity\Tree');
    }

    /**
     * @return Response
     *
     * @Route("/index")
     * @Template()
     */
    public function indexAction()
    {
        $res = $this->getRepository()->findAll();

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
     * TODO : must return with 1 parameter
     * [{"attr":{"id":"node_2","rel":"drive"},"data":"C:","state":"closed"},{"attr":{"id":"node_6","rel":"drive"},"data":"D:","state":""}]
     */
    public function childrenAction(Request $request)
    {
        // initialize variables
        $parentId = $request->get('id');
        $recursive = false;

//         SELECT * FROM tree
//         WHERE left >= $node["left"]
//         AND right <=  $node['right'] ---> remove if not recursive
//         ORDER BY left ASC

        $categories = $this->getRepository()->findBy(array('parent' => (integer) $parentId));

        $json = '[';
        foreach ($categories as $category) {
            $json .= '{"attr":{"id":"node_'. $category->getId() .'","rel":"'. $category->getType() .'"},"data":"'.$category->getTitle().'","state":"closed"},';
        }
        $json = substr($json, 0, -1);
        $json .= ']';

//         if ($recursive) {
//             $node = $this->_get_node($id);
//             $this->db->query("SELECT `".implode("` , `", $this->fields)."`
//                     FROM `".$this->table."`
//                     WHERE `".$this->fields["left"]."` >= ".(int) $node[$this->fields["left"]]."
//                     AND `".$this->fields["right"]."` <= ".(int) $node[$this->fields["right"]]."
//                     ORDER BY `".$this->fields["left"]."` ASC");
//         } else {
//             $this->db->query("SELECT `".implode("` , `", $this->fields)."`
//                     FROM `".$this->table."`
//                     WHERE `".$this->fields["parent_id"]."` = ".(int) $id."
//                     ORDER BY `".$this->fields["position"]."` ASC");
//         }
//         while ($this->db->nextr()) {
//             $children[$this->db->f($this->fields["id"])] = $this->db->get_row("assoc");
//         }

        return $this->render('PimCatalogTaxinomyBundle:Tree:children.html.twig', array('json_categories' => $json));
    }

    /**
     *
     * @param unknown_type $categoryId
     * @return boolean
     */
    protected function getNode($categoryId)
    {
        $this->db->query("SELECT `".implode("` , `", $this->fields)."` FROM `".$this->table."` WHERE `".$this->fields["id"]."` = ".(int) $id);
        $this->db->nextr();
        return $this->db->nf() === 0 ? false : $this->db->get_row("assoc");
    }
}