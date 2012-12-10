<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Command;

use Pim\Bundle\ConnectorIcecatBundle\Service\ConnectorService;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Pim\Bundle\ConnectorIcecatBundle\Entity\ConfigManager;
/**
 * Abstract class to implements command
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
abstract class AbstractPimCommand extends ContainerAwareCommand
{
    /**
     * @var DocumentManager
     */
    protected $documentManager;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
    }

    /**
     * @return DocumentManager
     */
    protected function getDocumentManager()
    {
        if (!$this->documentManager) {
            $this->documentManager = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        }

        return $this->documentManager;
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        if (! $this->entityManager) {
            $this->entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        }

        return $this->entityManager;
    }

    /**
     * @return ConfigManager
     */
    protected function getConfigManager()
    {
        return $this->getContainer()->get('pim.connector_icecat.config_manager');
    }

    /**
     * Alias to use write method with output interface defined in initialize method
     * @param string $string
     */
    protected function writeln($string = '')
    {
        $this->output->writeln($string);
    }

    /**
     * @return ConnectorService
     */
    protected function getConnectorService()
    {
        return $this->getContainer()->get('pim.connector_icecat.icecat_service');
    }
}