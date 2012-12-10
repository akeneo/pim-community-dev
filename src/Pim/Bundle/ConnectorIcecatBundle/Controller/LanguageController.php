<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use APY\DataGridBundle\Grid\Source\Entity as GridEntity;

use \Exception;
use Doctrine\DBAL\DBALException;
/**
 * Icecat language controller regroups all features for languages entities as loading and listing
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/language")
 */
class LanguageController extends Controller
{
    /**
     * Loading languages from icecat to local database
     *
     * @return Response
     *
     * @Route("/load-from-icecat")
     * @Template()
     */
    public function loadFromIcecatAction()
    {
        try {
            $srvConnector = $this->container->get('pim.connector_icecat.icecat_service');
            $srvConnector->importIcecatLanguages();
            $this->get('session')->setFlash('success', 'Base languages has been imported from Icecat');
        } catch (DBALException $e) {
            $this->container->get('logger')->err($e->getCode() .' : '. $e->getMessage());
            $this->get('session')->setFlash('error', 'Erreur en base de données lors de l\'import');
        } catch (Exception $e) {
            $this->get('session')->setFlash('error', $e->getMessage());
        }

        return $this->redirect($this->generateUrl('pim_connectoricecat_language_list'));
    }

    /**
     * List Icecat languages in a grid
     *
     * @return Response
     *
     * @Route("/list")
     * @Template()
     */
    public function listAction()
    {
        // creates simple grid based on entity (ORM)
        $source = new GridEntity('PimConnectorIcecatBundle:SourceLanguage');
        $grid = $this->get('grid');
        $grid->setSource($source);
        // manage the grid redirection, exports response of the controller
        return $grid->getGridResponse('PimConnectorIcecatBundle:Language:grid.html.twig');
    }
}
