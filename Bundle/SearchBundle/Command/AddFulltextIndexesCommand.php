<?php
namespace Oro\Bundle\SearchBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Symfony\Component\Console\Helper\HelperInterface;

class AddFulltextIndexesCommand extends ContainerAwareCommand
{
    /**
     * Console command configuration
     */
    public function configure()
    {
        $this->setName('oro:search:create-index');
        $this->setDescription('Creates fulltext index for search_index_text table');
    }

    /**
     * Update indexes for MySQL database
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $connection = $this->getContainer()->get('doctrine')->getConnection();
        $config = $connection->getParams();

        $configClasses = $this->getContainer()->getParameter('oro_search.engine_orm');
        if (isset($configClasses[$config['driver']])) {
            $className = $configClasses[$config['driver']];
            $dialog = $this->getDialogHelper();

            $dialog->writeSection($output, 'Creating indexes for string index table');
            $connection->query($className::getPlainSql());

            $dialog->writeSection($output, 'Completed.');
        }
    }

    /**
     * @return DialogHelper|HelperInterface
     */
    protected function getDialogHelper()
    {
        $dialog = $this->getHelperSet()->get('dialog');
        if (!$dialog || get_class($dialog) !== 'Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper') {
            $this->getHelperSet()->set($dialog = new DialogHelper());
        }

        return $dialog;
    }
}
