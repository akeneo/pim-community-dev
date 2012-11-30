<?php
namespace Pim\Bundle\ConnectorIcecatBundle\ETL\Write;

use Pim\Bundle\ConnectorIcecatBundle\Document\IcecatProductDataSheet;
use Pim\Bundle\ConnectorIcecatBundle\Helper\MemoryHelper;
use Pim\Bundle\ConnectorIcecatBundle\Helper\TimeHelper;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Read\ProductSetXmlFromUrl;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Transform\ProductSetXmlToDataSheetTransformer;

/**
 * Aims to insert detailled icecat product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InsertDetailledIcecatProductsFromUrl
{

    /**
     * Import detailled data from url to local database
     *
     * @param ObjectManager $objectManager  manager
     * @param string        $baseProductUrl icecat base product url
     * @param string        $login          icecat login
     * @param string        $password       icecat password
     * @param integer       $limit          nb products to import
     * @param integer       $batchSize      batch size
     */
    public function import($objectManager, $baseProductUrl, $login, $password, $limit, $batchSize = 100)
    {
        // get products
        $datasheets = $objectManager->getRepository('PimConnectorIcecatBundle:IcecatProductDataSheet')
            ->findBy(array('status' => IcecatProductDataSheet::STATUS_INIT));
        echo $datasheets->count() .' products found'.PHP_EOL;

        // loop on products
        $nbProd = 0;

        TimeHelper::addValue('start-import');
        TimeHelper::addValue('loop-import');
        MemoryHelper::addValue('memory');

        foreach ($datasheets as $datasheet) {

            try {
                // get xml content
                $datasheetUrl = $baseProductUrl.$datasheet->getProductId() .'.xml';
                $reader = new ProductSetXmlFromUrl($datasheetUrl, $login, $password);
                $reader->extract();
                $content = simplexml_load_string($reader->getXmlContent());

                if (!$content) {
                    echo 'Exception -> '. $file . ' is not well formed';
                    $datasheet->setIsImported(-1);
                    $objectManager->persist($datasheet);

                } else {
                    // keep only used data, convert to array and encode ton json format
                    $xmlToArray = new ProductSetXmlToDataSheetTransformer($content, $datasheet);
                    $xmlToArray->enrich();

                    // persist details
                    //$product->setData(json_encode($data));
                    //$product->setStatus(IcecatProductDataSheet::STATUS_IMPORT);
                    $objectManager->persist($datasheet);
                    //$this->writeln('insert '. $product->getProductId());

                    // save by batch of x product details
                    if (++$nbProd === $batchSize) {
                        $objectManager->flush();
                        $objectManager->clear();

                        echo 'After flush range of '.$batchSize.' '. MemoryHelper::writeGap('memory').' '. TimeHelper::writeGap('loop-import').PHP_EOL;
                        $nbProd = 0;
                    }

                    // stop when limit is attempted
                    // TODO : must be remove when query with where clause and limit work
                    if (--$limit === 0) {
                        $objectManager->flush();
                        $objectManager->clear();
                        break;
                    }
                }
            } catch (\Exception $e) {
                echo 'Exception -> '. $e->getMessage().PHP_EOL;
                $datasheet->setStatus(IcecatProductDataSheet::STATUS_ERROR);
                $objectManager->persist($datasheet);
            }
        }
        echo 'total time elapsed : '. TimeHelper::writeGap('start-import').PHP_EOL;
    }

}
