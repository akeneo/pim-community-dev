<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Query;

use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\HelperSet;

/**
 * Dump attribute filters in console output
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeFilterDumper implements DumperInterface
{
    /** @var QueryFilterRegistryInterface */
    protected $registry;

    /** @var AttributeRepository */
    protected $repository;

    /**
     * @param QueryFilterRegistryInterface $registry
     * @param AttributeRepository          $repository
     */
    public function __construct(QueryFilterRegistryInterface $registry, AttributeRepository $repository)
    {
        $this->registry = $registry;
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function dump(OutputInterface $output, HelperSet $helperSet)
    {
        $output->writeln("<info>Useable attributes filters...<info>");
        $attributes = $this->repository->findAll();
        $rows = [];
        foreach ($attributes as $attribute) {
            $field = $attribute->getCode();
            $filter = $this->registry->getAttributeFilter($attribute);
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
        $table = $helperSet->get('table');
        $headers = ['attribute', 'localizable', 'scopable', 'attribute type', 'filter_class', 'operators'];
        $table->setHeaders($headers)->setRows($rows);
        $table->render($output);
    }
}
