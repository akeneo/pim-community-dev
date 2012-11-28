<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Command;

use Pim\Bundle\CatalogTaxinomyBundle\Entity\Category;

use Pim\Bundle\ConnectorIcecatBundle\Entity\Config;
use Pim\Bundle\ConnectorIcecatBundle\Helper\MemoryHelper;
use Pim\Bundle\ConnectorIcecatBundle\Helper\TimeHelper;
use Pim\Bundle\ConnectorIcecatBundle\Transform\CategoriesXmlToCategoriesTransformer;

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
        $downloadUrl      = $this->getConfigManager()->getValue(Config::CATEGORIES_URL);
        $baseDir          = $configManager->getValue(Config::BASE_DIR);
        $archivedFilePath = $baseDir . $configManager->getValue(Config::CATEGORIES_ARCHIVED_FILE);
        $filePath         = $baseDir . $configManager->getValue(Config::CATEGORIES_FILE);

        // download source
        $this->downloadFile($downloadUrl, $archivedFilePath);

        // unpack source
        $this->unpackFile($archivedFilePath, $filePath);

        // read source
        $xmlContent = $this->readFile($filePath);

        // import categories
        TimeHelper::addValue('import-base');

        // transform xml to category entities
        $categories = $this->transformXmlToCategories($xmlContent);

        // persist entities with constraint validation
        foreach ($categories as $category) {
            $this->getEntityManager()->persist($category);
        }
        $this->flush();
        $this->writeln('command executed successfully : '. count($categories) .' categories inserted');
        $this->writeln(TimeHelper::writeGap('import-base'));
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
     * @return \SimpleXMLElement
     */
    protected function readFile($filePath)
    {
        $content = file_get_contents($filePath);

        return simplexml_load_string($content);
    }

    /**
     * Transform XML content to category entities
     * @param \SimpleXMLElement $xmlContent
     *
     * @return array
     */
    protected function transformXmlToCategories(\SimpleXMLElement $xmlContent)
    {
        $transformer = new CategoriesXmlToCategoriesTransformer($xmlContent);

        return $transformer->transform();
    }

    /**
     * Call entity manager to flush data
     */
    protected function flush()
    {
        $this->getEntityManager()->flush();
        $this->writeln('Before clear -> '. MemoryHelper::writeValue('memory'));
        $this->getEntityManager()->clear();
        $this->writeln('After clear  -> '. MemoryHelper::writeGap('memory'));
    }
}
