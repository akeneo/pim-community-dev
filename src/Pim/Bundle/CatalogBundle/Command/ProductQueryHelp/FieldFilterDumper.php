<?php

namespace Pim\Bundle\CatalogBundle\Command\ProductQueryHelp;

use Pim\Bundle\CatalogBundle\Command\DumperInterface;
use Pim\Component\Catalog\Query\Filter\FilterRegistryInterface;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Dump field filters in console output
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FieldFilterDumper implements DumperInterface
{
    /** @var FilterRegistryInterface */
    protected $registry;

    /**
     * @param FilterRegistryInterface $registry
     */
    public function __construct(FilterRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function dump(OutputInterface $output, HelperSet $helperSet)
    {
        $output->writeln("<info>Useable field filters...</info>");

        $rows = [];
        foreach ($this->registry->getFieldFilters() as $filter) {
            $class = get_class($filter);
            $operators = implode(', ', $filter->getOperators());
            foreach ($filter->getFields() as $field) {
                $rows[] = [$field, $operators, $class];
            }
        }
        $headers = ['field', 'operators', 'filter_class'];
        $table = $helperSet->get('table');
        $table->setHeaders($headers)->setRows($rows);
        $table->render($output);
    }
}
