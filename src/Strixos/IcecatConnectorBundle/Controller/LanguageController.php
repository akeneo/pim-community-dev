<?php
namespace Strixos\IcecatConnectorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use APY\DataGridBundle\Grid\Source\Entity as GridEntity;

use \Exception;
/**
 * Icecat language controller regroups all features for languages entities as loading and listing 
 * 
 * @author    Romain Monceau @ Akeneo
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LanguageController extends Controller
{
    /**
     * Loading languages from icecat to local database
     * 
     * @Route("/language/load-from-icecat")
     * @Template()
     */
    public function loadFromIcecatAction()
    {
        try {
            $srvConnector = $this->container->get('akeneo.connector.icecat_service');
            $srvConnector->importLanguages();
            $this->get('session')->setFlash('notice', 'Base languages has been imported from Icecat');
        } catch (Exception $e) {
            $this->get('session')->setFlash('exception', $e->getMessage());
        }
        return $this->redirect($this->generateUrl('strixos_icecatconnector_language_list'));
    }

    /**
     * List Icecat languages in a grid
     * 
     * @Route("/language/list")
     * @Template()
     */
    public function listAction()
    {
        // creates simple grid based on entity (ORM)
        $source = new GridEntity('StrixosIcecatConnectorBundle:SourceLanguage');
        $grid = $this->get('grid');
        $grid->setSource($source);
        // manage the grid redirection, exports response of the controller
        return $grid->getGridResponse('StrixosIcecatConnectorBundle:Language:grid.html.twig');
    }
}