<?php
namespace Strixos\IcecatConnectorBundle\Model\Load;

/**
 * Aims to populate local referencial of suppliers by loading csv file
 *
 * @author    Nicolas Dupont @ Strixos
 * @copyright Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
use Strixos\IcecatConnectorBundle\Entity\Supplier;

class SupplierLoadDataInFile
{

    protected $_entityManager;

    /**
     * Aims to inject entity manager
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->_entityManager = $em;
    }

    /**
     * Load csv file to table
     *
     * @param string $csvFile
     */
    public function process($csvFile)
    {
        // truncate base suppliers
        $connection = $this->_entityManager->getConnection();
        $platform   = $connection->getDatabasePlatform();
        // TODO: get table name from entity ?
        $tableName = 'StrixosIcecatConnector_Supplier';
        $connection->executeUpdate($platform->getTruncateTableSQL($tableName));
        // load csv file into table
        $sql = "LOAD DATA LOCAL INFILE '".$csvFile."' INTO TABLE ".$tableName."
            FIELDS TERMINATED BY ',' ENCLOSED BY '\"';";
        $stmt = $connection->prepare($sql);
        $stmt->execute();
    }

}
