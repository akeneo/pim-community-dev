<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Tests\ETL\Write;

use Doctrine\ORM\EntityManager;

use Pim\Bundle\ConnectorIcecatBundle\ETL\Write\LanguagesFromXmlWriter;

use Pim\Bundle\CatalogBundle\Tests\KernelAwareTest;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LanguagesFromXmlWriterTest extends KernelAwareTest
{
    /**
     * test related method
     */
    public function testWrite()
    {
        // initialize variables
        $filename = 'languages-list.xml';
        $entityManager = $this->getEntityManager();
        $batchSize     = 10;

        // load xml content
        $content = $this->loadFile($filename);

        // call tested class
        $writer = new LanguagesFromXmlWriter($entityManager);
        $writer->write($content, $batchSize);
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->container->get('doctrine.orm.entity_manager');
    }

    /**
     * Load a file in SimpleXmlElement
     * @param string $filename
     *
     * @return string
     */
    protected function loadFile($filename)
    {
        $filepath = dirname(__FILE__) .'/../../Files/'. $filename;

        return file_get_contents($filepath);
    }

}
