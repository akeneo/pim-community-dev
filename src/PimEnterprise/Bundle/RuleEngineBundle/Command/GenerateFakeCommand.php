<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\RuleEngineBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\Rule;

/**
 * Command to generate fake rules
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class GenerateFakeCommand extends ContainerAwareCommand
{
    protected $values = [
        'pim_catalog_text'             => ['sed', 'qui', 'quasi', 'ab', 'nobis', 'nam', 'deleniti', 'vero'],
        'pim_catalog_identifier'       => ['sku', '-', 'sku-4372', 'sku-', ''],
        'pim_catalog_textarea'         => ['sed', 'qui', 'quasi', 'ab', 'nobis', 'nam', 'deleniti', 'vero', ''],
        'pim_catalog_metric'           => ['1.0', '2', '1000', '123'],
        'pim_catalog_number'           => ['1.0', '2', '1000', '123'],
        'pim_catalog_date'             => ['2014-10-07', '1990-05-22'],
        'pim_catalog_boolean'          => [true, false],
        'pim_catalog_simpleselect'     => ['red', 'blue'],
        'pim_catalog_multiselect'      => ['green', 'blue'],
    ];

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:rule-dev:generate-fake')
            ->addArgument('count', InputArgument::OPTIONAL, 'Number of rules to generate', 100)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // get rule instance
        $count = $input->getArgument('count');

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $attributes = $em->getRepository('PimCatalogBundle:Attribute')->findAll();

        $filters = $this->getFilters($attributes);

        $rules = [];
        $cpt = 0;
        while ($cpt < $count) {
            $rule = new Rule();
            $rule->setCode('rule_' . $cpt);
            $rule->setContent(json_encode([
                'conditions' => $this->getConditions($filters, 2)
            ]));
            $rule->setType('product');

            $em->persist($rule);

            $cpt++;
        }

        $connection = $em->getConnection();
        $platform   = $connection->getDatabasePlatform();

        $connection->executeUpdate($platform->getTruncateTableSQL(
            $em->getClassMetadata('PimEnterprise\Bundle\RuleEngineBundle\Model\Rule')->getTableName()
        ));

        $em->flush();
    }

    /**
     * Get a collection of condition for a fake rule
     * @param array  $filters
     * @param integer $max
     *
     * @return array
     */
    protected function getConditions(array $filters, $max)
    {
        $cpt = rand(1, $max);

        $conditions = [];
        $attributes = [];
        while ($cpt > 0) {
            do {
                $condition = $filters[array_rand($filters)];
            } while (in_array($condition['field'], $attributes));

            $conditions[] = $condition;
            $attributes[] = $condition['field'];
            $cpt--;
        }

        return $conditions;
    }

    /**
     * Get all possible filters
     * @param array $attributes
     *
     * @return array
     */
    protected function getFilters($attributes)
    {
        $filterRegistry = $this->getContainer()->get('pim_catalog.doctrine.query.filter_registry');
        $filters = [];
        foreach ($attributes as $attribute) {
            if ($filter = $filterRegistry->getAttributeFilter($attribute)) {
                foreach ($filter->getOperators() as $operator) {
                    if (isset($this->values[$attribute->getAttributeType()]) && !$attribute->isScopable()) {
                        foreach ($this->values[$attribute->getAttributeType()] as $value) {
                            $value = $this->getValue($operator, $attribute, $value);

                            $filters[] = [
                                'field'    => $attribute->getCode(),
                                'operator' => $operator,
                                'value'    => $value
                            ];
                        }
                    }
                }
            }
        }

        return $filters;
    }

    protected function getValue($operator, $attribute, $value)
    {
        switch ($operator) {
            case 'IN':
                $value = [$value];
                break;
            case 'EMPTY':
                $value = null;
                break;
            default:
                break;
        }

        return $value;
    }
}
