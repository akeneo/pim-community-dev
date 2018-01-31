<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Helps to query products
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QueryHelpProductCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:product:query-help')
            ->setDescription('Display useable product query filters');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getFieldFilterDumper()->dump($output);
        $this->getAttributeFilterDumper()->dump($output);
    }

    /**
     * @return DumperInterface
     */
    protected function getFieldFilterDumper()
    {
        return $this->getContainer()->get('pim_catalog.query.filter.field_dumper');
    }

    /**
     * @return DumperInterface
     */
    protected function getAttributeFilterDumper()
    {
        return $this->getContainer()->get('pim_catalog.query.filter.attribute_dumper');
    }
}
