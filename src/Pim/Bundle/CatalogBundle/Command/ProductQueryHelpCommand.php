<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Pim\Bundle\CatalogBundle\Doctrine\Query\DumperInterface;
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
class ProductQueryHelpCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:debug:product-query-help')
            ->setDescription('Display useable product query filters');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helperSet = $this->getHelperSet();
        $this->getFieldFilterDumper()->dump($output, $helperSet);
        $this->getAttributeFilterDumper()->dump($output, $helperSet);
    }

    /**
     * @return DumperInterface
     */
    protected function getFieldFilterDumper()
    {
        return $this->getContainer()->get('pim_catalog.doctrine.query.field_filter_dumper');
    }

    /**
     * @return DumperInterface
     */
    protected function getAttributeFilterDumper()
    {
        return $this->getContainer()->get('pim_catalog.doctrine.query.attribute_filter_dumper');
    }
}
