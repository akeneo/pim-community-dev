<?php
namespace Strixos\IcecatConnectorBundle\Model\Import;

/**
 * Aims to populate local referencial of suppliers by loading csv file
 *
 * @author    Nicolas Dupont @ Strixos
 * @copyright Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SupplierLoadDataInFile extends DataImport
{
    /**
     * (non-PHPdoc)
     * @see \Strixos\IcecatConnectorBundle\Model\Import\DataImport::process()
     */
    public function process($csvFile)
    {
        // truncate base suppliers
        $connection = $this->entityManager->getConnection();
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