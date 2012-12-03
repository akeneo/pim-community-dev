<?php
namespace Pim\Bundle\ConnectorIcecatBundle\ETL\Write;

use Pim\Bundle\ConnectorIcecatBundle\Document\IcecatProductDataSheet;
use Pim\Bundle\ConnectorIcecatBundle\Helper\MemoryHelper;
use Pim\Bundle\ConnectorIcecatBundle\Helper\TimeHelper;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Read\ProductSetXmlFromUrl;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Transform\ProductSetXmlToDataSheetTransformer;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Transform\ProductValuesXmlToDataSheetTransformer;

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
        // get datasheets
        $datasheets = $objectManager->getRepository('PimConnectorIcecatBundle:IcecatProductDataSheet')
            ->findBy(array('status' => IcecatProductDataSheet::STATUS_INIT));
        echo $datasheets->count() .' products found'.PHP_EOL;

        // loop on datasheets
        $nbProd = 0;

        TimeHelper::addValue('start-import');
        TimeHelper::addValue('loop-import');
        MemoryHelper::addValue('memory');

        $locales = array('US' => 1, 'FR' => 3);

        foreach ($datasheets as $datasheet) {

            try {
                // get xml content (set)
                $datasheetUrl = $baseProductUrl.'INT/'.$datasheet->getProductId() .'.xml';
                $reader = new ProductSetXmlFromUrl($datasheetUrl, $login, $password);
                $reader->extract();
                $content = simplexml_load_string($reader->getXmlContent());

                if (!$content) {
                    echo 'Exception -> '. $file . ' is not well formed';
                    $datasheet->setStatus(IcecatProductDataSheet::STATUS_ERROR);
                    $objectManager->persist($datasheet);

                } else {

                    // enrich with set data
                    $xmlToArray = new ProductSetXmlToDataSheetTransformer($content, $datasheet);
                    $xmlToArray->enrich();

                    // enrich with product values in any locales
                    foreach ($locales as $localeCode => $localeId) {

                        // get xml content (set)
                        $datasheetUrl = $baseProductUrl.'/'.$localeCode.'/'.$datasheet->getProductId() .'.xml';
                        $reader = new ProductSetXmlFromUrl($datasheetUrl, $login, $password);
                        $reader->extract();
                        $content = simplexml_load_string($reader->getXmlContent());

                        if (!$content) {
                            echo 'Exception -> '. $file . ' is not well formed';
                            $datasheet->setStatus(IcecatProductDataSheet::STATUS_ERROR);
                            $objectManager->persist($datasheet);

                        } else {
                            // enrich with product values
                            $xmlToArray = new ProductValuesXmlToDataSheetTransformer($content, $datasheet, $localeId);
                            $xmlToArray->enrich();
                        }
                    }

                    // persist details
                    $objectManager->persist($datasheet);

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
