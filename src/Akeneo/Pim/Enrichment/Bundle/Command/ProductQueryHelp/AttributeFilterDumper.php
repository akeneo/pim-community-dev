<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command\ProductQueryHelp;

use Akeneo\Pim\Enrichment\Bundle\Command\DumperInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FilterRegistryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Dump attribute filters in console output
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeFilterDumper implements DumperInterface
{
    /** @var FilterRegistryInterface */
    protected $registry;

    /** @var AttributeRepositoryInterface */
    protected $repository;

    /**
     * @param FilterRegistryInterface      $registry
     * @param AttributeRepositoryInterface $repository
     */
    public function __construct(FilterRegistryInterface $registry, AttributeRepositoryInterface $repository)
    {
        $this->registry = $registry;
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function dump(OutputInterface $output)
    {
        $output->writeln("<info>Useable attributes filters...</info>");
        $attributeFilters = $this->getAttributeFilters();
        $attributes = $this->repository->findAll();

        $rows = [];
        foreach ($attributes as $attribute) {
            $rows = array_merge($rows, $this->getFilterInformationForAttribute($attribute, $attributeFilters));
        }

        $table = new Table($output);
        $headers = ['attribute', 'localizable', 'scopable', 'attribute type', 'operators', 'filter_class'];
        $table->setHeaders($headers)->setRows($rows);
        $table->render();
    }

    /**
     * Returns all registered filters indexed by their supported attributes
     *
     * @return array
     */
    protected function getAttributeFilters()
    {
        $attributeFilters = [];
        foreach ($this->registry->getAttributeFilters() as $filter) {
            $supportedAttributes = $filter->getAttributeTypes();

            if (null !== $supportedAttributes) {
                foreach ($supportedAttributes as $attribute) {
                    $attributeFilters[$attribute][] = $filter;
                }
            }
        }

        return $attributeFilters;
    }

    /**
     * Returns available information for the attribute and filters which supports it
     *
     * @param AttributeInterface $attribute
     * @param array              $attributeFilters
     *
     * @return array
     */
    protected function getFilterInformationForAttribute(AttributeInterface $attribute, array $attributeFilters)
    {
        $field = $attribute->getCode();
        $attributeType = $attribute->getType();
        $isLocalizable = $attribute->isLocalizable() ? 'yes' : 'no';
        $isScopable = $attribute->isScopable() ? 'yes' : 'no';

        $newEntries = [];
        if (array_key_exists($attributeType, $attributeFilters)) {
            foreach ($attributeFilters[$attributeType] as $filter) {
                $class = get_class($filter);
                $operators = implode(', ', $filter->getOperators());

                $newEntries[] = [
                    $field,
                    $isLocalizable,
                    $isScopable,
                    $attributeType,
                    $operators,
                    $class
                ];
            }

            return $newEntries;
        }

        if ($attribute->isBackendTypeReferenceData()) {
            foreach ($this->registry->getAttributeFilters() as $filter) {
                if ($filter->supportsAttribute($attribute)) {
                    $class = get_class($filter);
                    $operators = implode(', ', $filter->getOperators());

                    $newEntries[] = [
                        $field,
                        $isLocalizable,
                        $isScopable,
                        $attributeType,
                        $operators,
                        $class
                    ];
                }
            }

            return $newEntries;
        }

        return [
            [
                $field,
                $isLocalizable,
                $isScopable,
                $attributeType,
                '',
                'Not supported'
            ]
        ];
    }
}
