<?php
namespace Strixos\DataFlowBundle\Model\Load;

use Strixos\CatalogBundle\Document\Product;
use Strixos\CatalogBundle\DataFixtures\ORM\LoadAttributeSetData;
use Strixos\DataFlowBundle\Entity\Step;

/**
 *
 * @author    Nicolas Dupont @ Strixos
 * @copyright Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductImporter extends Step
{

    /**
     * (non-PHPdoc)
     * @see Strixos\DataFlowBundle\Entity.Step::run()
     * @param array $inputData
     */
    public function run($inputData = null)
    {
        // inputData contain product rows, key for attcode
        $manager = $this->getJob()->getDocumentManager(); // TODO i don't want to know real persistence
        $nbInsert = 0;
        $range = 500; // flush by range
        foreach ($inputData as $productData) {
            // create product
            $product = new Product();
            $product->setAttributeSetCode(LoadAttributeSetData::ATTRIBUTE_SET_BASE);
            foreach ($productData as $attCode => $value) {
                if ($attCode == 'sku') { // TODO : define as others values ?
                    $product->setSku($value);
                } else {
                    $product->addValue($attCode, $value);
                }
            }
            // persist but flush not each time
            $manager->persist($product);
            if ($nbInsert++ == $range) {
                $manager->flush();
                $nbInsert = 0;
            }
        }
        $manager->flush();
        // add notice message
        $msg = __CLASS__.' : insert '.count($inputData).' products from input data';
        $this->addMessage($msg);
        return true;
    }

}