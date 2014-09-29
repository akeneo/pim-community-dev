<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Doctrine\Query\ProductQueryFilterRegistryInterface;
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
        $this->printFieldFilters($output);
        $this->printAttributeFilters($output);
    }

    /**
     * Print the field filters
     *
     * @param OutputInterface $output
     */
    protected function printFieldFilters(OutputInterface $output)
    {
        $output->writeln("<info>Useable field filters...<info>");
        $registry = $this->getFilterRegistry();
        $fields = ['id', 'created', 'updated', 'enabled', 'completeness', 'family', 'groups'];
        $rows = [];
        foreach ($fields as $field) {
            $filter = $registry->getFieldFilter($field);
            if ($filter) {
                $class = get_class($filter);
                $operators = implode(', ', $filter->getOperators());
            } else {
                $class = 'Not supported';
                $operators = '';
            }
            $rows[]= [$field, $class, $operators];
        }
        $table = $this->getHelper('table');
        $headers = ['field', 'filter_class', 'operators'];
        $table->setHeaders($headers)->setRows($rows);
        $table->render($output);
    }

    /**
     * Print the attribute filters
     *
     * @param OutputInterface $output
     */
    protected function printAttributeFilters(OutputInterface $output)
    {
        $output->writeln("<info>Useable attributes filters...<info>");
        $registry = $this->getFilterRegistry();
        $attributes = $this->getAttributeRepository()->findAll();
        $rows = [];
        foreach ($attributes as $attribute) {
            $field = $attribute->getCode();
            $filter = $registry->getAttributeFilter($attribute);
            if ($filter) {
                $class = get_class($filter);
                $operators = implode(', ', $filter->getOperators());
            } else {
                $class = 'Not supported';
                $operators = '';
            }
            $rows[]= [
                $field,
                $attribute->isLocalizable() ? 'yes' : 'no',
                $attribute->isScopable() ? 'yes' : 'no',
                $attribute->getAttributeType(),
                $class,
                $operators
            ];
        }
        $table = $this->getHelper('table');
        $headers = ['attribute', 'localizable', 'scopable', 'attribute type', 'filter_class', 'operators'];
        $table->setHeaders($headers)->setRows($rows);
        $table->render($output);
    }

    /**
     * @return ProductQueryFilterRegistryInterface
     */
    protected function getFilterRegistry()
    {
        return $this->getContainer()->get('pim_catalog.doctrine.query.product_filter_registry');
    }

    /**
     * @return AttributeRepository
     */
    protected function getAttributeRepository()
    {
        return $this->getContainer()->get('pim_catalog.repository.attribute');
    }
}
