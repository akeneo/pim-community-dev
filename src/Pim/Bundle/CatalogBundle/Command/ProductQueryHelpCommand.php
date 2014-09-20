<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;

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
        $output->writeln("<info>Useable field filters...<info>");
        $pqb = $this->getProductQueryBuilder();

        $fields = ['id', 'created', 'updated', 'enabled', 'completeness', 'family', 'groups'];
        $rows = [];
        foreach ($fields as $field) {
            // TODO a filter and a sorter registry, PQB use them ?
            $filter = $pqb->getFilter($field);
            if ($filter) {
                $class = get_class($filter);
                $operators = implode(', ', $pqb->getFilter($field)->getOperators());
            } else {
                $class = 'Not supported';
                $operators = '';
            }
            $filter = $pqb->getFilter($field);
            $rows[]= [$field, $class, $operators];
        }
        $table = $this->getHelper('table');
        $table->setHeaders(['field', 'filter_class', 'operators'])->setRows($rows);
        $table->render($output);

        $output->writeln("<info>Useable attributes filters...<info>");
        $attributes = $this->getAttributeRepository()->findAll();
        $rows = [];
        foreach ($attributes as $attribute) {
            $field = $attribute->getCode();
            // TODO a filter and a sorter registry, PQB use them ?
            $filter = $pqb->getFilter($field);
            if ($filter) {
                $class = get_class($filter);
                $operators = implode(', ', $pqb->getFilter($field)->getOperators());
            } else {
                $class = 'Not supported';
                $operators = '';
            }

            $rows[]= [$field, $attribute->getAttributeType(), $class, $operators];
        }
        $table = $this->getHelper('table');
        $table->setHeaders(['attribute', 'attribute type', 'filter_class', 'operators'])->setRows($rows);
        $table->render($output);
    }

    /**
     * @return ProductQueryBuilderInterface
     */
    protected function getProductQueryBuilder()
    {
        return $this->getContainer()->get('pim_catalog.doctrine.product_query_builder');
    }

    /**
     * @return AttributeRepository
     */
    protected function getAttributeRepository()
    {
        return $this->getContainer()->get('pim_catalog.repository.attribute');
    }
}
