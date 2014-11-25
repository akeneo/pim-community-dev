<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Query;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\HelperSet;

/**
 * Dump field filters in console output
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FieldFilterDumper implements DumperInterface
{
    /** @var QueryFilterRegistryInterface */
    protected $registry;

    /**
     * @param QueryFilterRegistryInterface $registry
     */
    public function __construct(QueryFilterRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function dump(OutputInterface $output, HelperSet $helperSet)
    {
        $output->writeln("<info>Useable field filters...<info>");
        $fields = ['id', 'created', 'updated', 'enabled', 'completeness', 'family', 'groups', 'categories'];
        $rows = [];
        foreach ($fields as $field) {
            $filter = $this->registry->getFieldFilter($field);
            if ($filter) {
                $class = get_class($filter);
                $operators = implode(', ', $filter->getOperators());
            } else {
                $class = 'Not supported';
                $operators = '';
            }
            $rows[]= [$field, $class, $operators];
        }
        $headers = ['field', 'filter_class', 'operators'];
        $table = $helperSet->get('table');
        $table->setHeaders($headers)->setRows($rows);
        $table->render($output);
    }
}
