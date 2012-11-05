<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use \Exception;

/**
 * Icecat controller used for global actions like truncate tables
 * 
 * @author    Romain Monceau @ Akeneo
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IcecatController extends Controller
{
    /**
     * Empty icecat tables
     * 
     * @Route("/empty-icecat-tables")
     * @Template()
     */
    public function emptyIcecatTablesAction()
    {
        $connection = $this->getDoctrine()->getEntityManager()->getConnection();
        $platform = $connection->getDatabasePlatform();
        
        try {
            $connection->beginTransaction();
            $connection->executeUpdate("SET foreign_key_checks = 0");
            
            $connection->executeUpdate($platform->getTruncateTableSQL('StrixosIcecatConnector_SourceLanguage', true));
            $connection->executeUpdate($platform->getTruncateTableSQL('StrixosIcecatConnector_SourceProduct', true));
            $connection->executeUpdate($platform->getTruncateTableSQL('StrixosIcecatConnector_SourceSupplier', true));
            
            $connection->executeUpdate("SET foreign_key_checks = 1");
            $connection->commit();
        } catch (Exception $e) {
            $connection->rollback();
            $this->get('session')->setFlash('exception', $e->getMessage());
        }
        
        return $this->redirect($this->generateUrl('pim_connectoricecat_default_index'));
    }
    
    /**
     * Empty all tables of the akeneo_pim database
     * 
     * @Route("/empty-tables")
     * @Template()
     */
    public function emptyTablesAction()
    {
        $connection = $this->getDoctrine()->getEntityManager()->getConnection();
        $platform = $connection->getDatabasePlatform();
        
        try {
            $connection->beginTransaction();
            $connection->executeUpdate("SET foreign_key_checks = 0");
            
            // truncate icecat tables
            $connection->executeUpdate($platform->getTruncateTableSQL('StrixosIcecatConnector_SourceLanguage', true));
            $connection->executeUpdate($platform->getTruncateTableSQL('StrixosIcecatConnector_SourceProduct', true));
            $connection->executeUpdate($platform->getTruncateTableSQL('StrixosIcecatConnector_SourceSupplier', true));
            
            // truncate pim tables
            $connection->executeUpdate($platform->getTruncateTableSQL('AkeneoCatalog_Product_Entity', true));
            $connection->executeUpdate($platform->getTruncateTableSQL('AkeneoCatalog_Product_Field', true));
            $connection->executeUpdate($platform->getTruncateTableSQL('AkeneoCatalog_Product_Group', true));
            $connection->executeUpdate($platform->getTruncateTableSQL('AkeneoCatalog_Product_Group_Field', true));
            $connection->executeUpdate($platform->getTruncateTableSQL('AkeneoCatalog_Product_Translation', true));
            $connection->executeUpdate($platform->getTruncateTableSQL('AkeneoCatalog_Product_Type', true));
            $connection->executeUpdate($platform->getTruncateTableSQL('AkeneoCatalog_Product_Value', true));
            
            $connection->executeUpdate("SET foreign_key_checks = 1");
            $connection->commit();
        } catch (Exception $e) {
            $connection->rollback();
            $this->get('session')->setFlash('exception', $e->getMessage());
        }
        
        return $this->redirect($this->generateUrl('pim_connectoricecat_default_index'));
    }
}
