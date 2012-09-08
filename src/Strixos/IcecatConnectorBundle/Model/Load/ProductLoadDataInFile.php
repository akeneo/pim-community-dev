<?php
namespace Strixos\IcecatConnectorBundle\Model\Load;

/**
 * Aims to populate local referencial of products by loading text file
 *
 * @author    Nicolas Dupont @ Strixos
 * @copyright Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
use Strixos\IcecatConnectorBundle\Entity\Supplier;

class ProductLoadDataInFile
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
        // TODO: get table name from entity ? sure to delete for products ?
        $tableName = 'StrixosIcecatConnector_Product';
        $connection->executeUpdate($platform->getTruncateTableSQL($tableName));
        // load csv file into table (ignore most of columns)
        $sql = "LOAD DATA LOCAL INFILE '".$csvFile."' INTO TABLE ".$tableName."
            FIELDS TERMINATED BY '\t' ENCLOSED BY '\"' IGNORE 1 LINES
            (@c1,@c2,@c3,@c4,@c5,@c6,@c7,@c8,@c9,@c10,@c11,@c12,@c13,@c14,@c15,@c16,@c17,@c18)
            set id='', product_id=@c2, product_name=@c13, supplier_id=@c14;";
        $stmt = $connection->prepare($sql);
        $stmt->execute();
    }

}
