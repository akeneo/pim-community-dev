<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Command;

use Pim\Bundle\CatalogTaxinomyBundle\Entity\Category;

use Pim\Bundle\ConnectorIcecatBundle\Entity\Config;
use Pim\Bundle\ConnectorIcecatBundle\Helper\MemoryHelper;
use Pim\Bundle\ConnectorIcecatBundle\Helper\TimeHelper;

use Pim\Bundle\DataFlowBundle\Model\Extract\FileHttpDownload;
use Pim\Bundle\DataFlowBundle\Model\Extract\FileUnzip;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
/**
 * Import all categories from icecat
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImportCategoriesCommand extends AbstractPimCommand
{
    /**
     * List of categories
     * @var array
     */
    protected $categories = array();

    /**
     * @staticvar array
     */
    protected static $langs = array('en_US' => 1);

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('connectoricecat:importCategories')
            ->setDescription('Import categories from icecat to localhost database');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // get config
        $configManager    = $this->getConfigManager();
//         $downloadUrl      = $this->getConfigManager()->getValue(Config::PRODUCTS_URL);
        $downloadUrl      = 'http://data.icecat.biz/export/freexml/refs/CategoriesList.xml.gz';
        $baseDir          = $configManager->getValue(Config::BASE_DIR);
//         $archivedFilePath = $baseDir . $configManager->getValue(Config::PRODUCTS_ARCHIVED_FILE);
//         $filePath         = $baseDir . $configManager->getValue(Config::PRODUCTS_FILE);
        $archivedFilePath = $baseDir . 'categories-list.xml.gz';
        $filePath         = $baseDir . 'categories-list.xml';

        // download source
        $this->downloadFile($downloadUrl, $archivedFilePath);

        // unpack source
        $this->unpackFile($archivedFilePath, $filePath);

        // read source
        $xmlContent = $this->readFile($filePath);

        // import categories
        TimeHelper::addValue('import-base');

        $count = 0;
        foreach ($xmlContent->Response->CategoriesList->Category as $xmlCategory) {
            // create category entity
            $category  = $this->createCategory((string) $xmlCategory['ID'], $xmlCategory);

            // create parent category entity
            $xmlParent = $xmlCategory->ParentCategory;
            $parent    = $this->createCategory((string) $xmlParent['ID'], $xmlParent->Names);
            $category->setParent($parent);

            // persist category
            $this->getEntityManager()->persist($category);

            $count++;
        }

        // persist documents with constraint validation
        $this->flush();
        $this->writeln('command executed successfully : '. $count .' categories inserted');
        $this->writeln(TimeHelper::writeGap('import-base'));
    }

    /**
     * Create a category entity
     * @param string           $icecatId        icecat id
     * @param SimpleXMLElement $xmlElementNames title of the category
     *
     * @return Category
     */
    protected function createCategory($icecatId, $xmlElementNames)
    {
        // get category if already exists else instanciate new
        if (isset($this->categories[$icecatId])) {
            $category = $this->categories[$icecatId];
        } else {
            $category = new Category();
        }

        // set translatable title
        foreach ($xmlElementNames as $name) {
            if (in_array((integer) $name['langid'], self::$langs)) {
                $title = isset($name['Value']) ? $name['Value'] : $name;
                $category->setTitle($title);
            }
        }

        // add category to list
        $this->categories[$icecatId] = $category;

        return $category;
    }

    /**
     * Download remote file
     * @param string $downloadUrl      url of the file on remote domain
     * @param string $archivedFilePath path for local archived file
     */
    protected function downloadFile($downloadUrl, $archivedFilePath)
    {
        // get config for optional options
        $login            = $this->getConfigManager()->getValue(Config::LOGIN);
        $password         = $this->getConfigManager()->getValue(Config::PASSWORD);

        // download xml file
        TimeHelper::addValue('download-file');
        $downloader = new FileHttpDownload();
        $downloader->process($downloadUrl, $archivedFilePath, $login, $password, false);
        $this->writeln('Download File -> '. TimeHelper::writeGap('download-file'));
    }

    /**
     * Unpack downloaded file
     * @param string $archivedFilePath path for local archived file
     * @param string $filePath         path for local unpacked file
     */
    protected function unpackFile($archivedFilePath, $filePath)
    {
        TimeHelper::addValue('unpack');
        MemoryHelper::addValue('unpack');
        $unpacker = new FileUnzip();
        $unpacker->process($archivedFilePath, $filePath, false);
        $this->writeln('Unpack File -> '. TimeHelper::writeGap('unpack') .' - '. MemoryHelper::writeGap('unpack'));
    }

    /**
     * Read downloaded and unpacked file
     * @param string $filePath
     *
     * @return SimpleXMLElement
     */
    protected function readFile($filePath)
    {
        $content = file_get_contents($filePath);

        return simplexml_load_string($content);
    }

    /**
     * Call document manager to flush data
     */
    protected function flush()
    {
        $this->getEntityManager()->flush();
        $this->writeln('Before clear -> '. MemoryHelper::writeValue('memory'));
        $this->getEntityManager()->clear();
        $this->writeln('After clear  -> '. MemoryHelper::writeGap('memory'));
    }
}
