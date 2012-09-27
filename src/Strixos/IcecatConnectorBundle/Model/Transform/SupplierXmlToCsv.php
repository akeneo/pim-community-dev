<?php
namespace Strixos\IcecatConnectorBundle\Model\Transform;

/**
 * Aims to transform suppliers xml file to csv file
 *
 * @author    Nicolas Dupont @ Strixos
 * @copyright Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class SupplierXmlToCsv
{
    /**
     * Transform xml file to csv
     *
     * @param string $xmlFile
     * @param string $csvFile
     */
    public function process($xmlFile, $csvFile)
    {
        // create csv file
        $fp = fopen($csvFile, 'w');
        // read from xml file
        $xmlString = file_get_contents($xmlFile);
        libxml_use_internal_errors(true);
        $xmlDoc = simplexml_load_string(utf8_encode($xmlString));
        $supplierList = $xmlDoc->SupplierMappings->SupplierMapping;
        foreach ($supplierList as $supplier) {
            $supplierSymbolList = $supplier->Symbol;
            $suppId = $supplier['supplier_id'];
            $suppName = $supplier['name'];
            foreach ($supplierSymbolList as $symbolTag) {
                $symbol = (string) $symbolTag;
                $distrId = $symbolTag['distributor_id'];
                // write into csv
                fputcsv($fp, array('', $suppId, $suppName, $distrId, $symbol));
            }
        }
        // then close csv stream
        fclose($fp);
    }
}
