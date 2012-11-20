<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Command;

use Pim\Bundle\ConnectorIcecatBundle\Transform\ProductArrayToCatalogProductTransformer;

use Pim\Bundle\ConnectorIcecatBundle\Transform\ProductXmlToArrayTransformer;

use Pim\Bundle\DataFlowBundle\Model\Extract\FileHttpReader;

use Pim\Bundle\CatalogBundle\Doctrine\ProductManager;

use Pim\Bundle\DataFlowBundle\Model\Extract\FileHttpDownload;

use Pim\Bundle\ConnectorIcecatBundle\PimConnectorIcecatBundle;

use Doctrine\ODM\MongoDB\DocumentManager;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Pim\Bundle\ConnectorIcecatBundle\Document\ProductDataSheet;
use Pim\Bundle\ConnectorIcecatBundle\Entity\ConfigManager;
use Pim\Bundle\ConnectorIcecatBundle\Entity\Config;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Doctrine\ODM\MongoDB\Query\Builder;
/**
 * Import detailled data for asked products
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImportDetailledProductsCommand extends ContainerAwareCommand
{
    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this->setName('connectoricecat:importDetailledProducts')
            ->setDescription('Import detailled data for a set of products')
            ->addArgument(
                'limit',
                InputArgument::REQUIRED,
                'Number of products to be imported'
            );
    }
    
    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // get arguments
        $limit = $input->getArgument('limit');
        
        // get config
        $configManager = $this->getConfigManager();
        $login       = $configManager->getValue(Config::LOGIN);
        $password    = $configManager->getValue(Config::PASSWORD);
        $baseDir     = $configManager->getValue(Config::BASE_DIR);
        $downloadUrl = $configManager->getValue(Config::BASE_URL) . $configManager->getValue(Config::BASE_PRODUCTS_URL);
        $baseFilePath= 'http://data.icecat.biz/export/freexml.int/INT/';
        
        // get data objects
        $dm = $this->getDocumentManager();
        $qb = $dm->getRepository('PimConnectorIcecatBundle:ProductDataSheet')->createQueryBuilder();
        $q = $qb->field('is_imported')->equals(null)
                ->limit($limit)
                ->getQuery();
        $products = $q->execute();
        
        echo count($products) ."\n";
        
        // prepare objects
        $reader = new FileHttpReader();
        $productManager = $this->getProductManager();
        libxml_use_internal_errors(true);
        
        
        // loop on products
        foreach ($products as $product)
        {
            $file = $product->getProductId() .'.xml';
            $content = $reader->process($baseFilePath . $file, $login, $password, false);
            $content = simplexml_load_string($content);
            
            $xmlToArray = new ProductXmlToArrayTransformer($content);
            $xmlToArray->transform();
            
            $baseData = $xmlToArray->getProductBaseData();
            $features = $xmlToArray->getProductFeatures();
            
            $arrayToProduct = new ProductArrayToCatalogProductTransformer($productManager, $baseData, $features, 'en_US');
            $arrayToProduct->transform();
            
            $product->setIsImported(true);
            $dm->persist($product);
            
            if (--$limit === 0) {
                $dm->flush();
                break;
            }
        }
    }
    
    /**
     * @return Doctrine\ODM\MongoDB\Query\Builder;
     */
    protected function createQB()
    {
        return $this->getDocumentManager()->getRepository('PimConnectorIcecatBundle:ProductDataSheet')->createQueryBuilder();
    }
    
    /**
     * @return ConfigManager
     */
    protected function getConfigManager()
    {
        return $this->getContainer()->get('pim.connector.icecat.configmanager');
    }
    
    /**
     * @return DocumentManager
     */
    protected function getDocumentManager()
    {
        return $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
    }
    
    /**
     * @return ProductManager
     */
    protected function getProductManager()
    {
        return $this->getContainer()->get('pim.catalog.product_manager');
    }
}