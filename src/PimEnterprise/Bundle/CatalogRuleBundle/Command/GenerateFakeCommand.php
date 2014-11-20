<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\Rule;

/**
 * Command to generate fake rules
 * TODO: remove this
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


        $fakeRules = false;
        if ($fakeRules) {

            $attributes = $em->getRepository('PimCatalogBundle:Attribute')->findAll();

            $filters = $this->getFilters($attributes);

            $rules = [];
            $cpt = 0;
            while ($cpt < $count) {
                $rule = new Rule();
                $rule->setCode('rule_' . $cpt);
                $rule->setContent(json_encode([
                    'conditions' => $this->getConditions($filters, 2),
                        'actions'    => [
                            ['type' => 'set_value', 'field' => 'name', 'value' => 'toto'],
                            [
                                'type'       => 'copy_value',
                                'from_field' => 'name',
                                'to_field'   => 'description',
                                'to_scope'   => 'mobile',
                                'to_locale'  => 'fr_FR'
                            ],
                        ]
                    ]));
                $rule->setType('product');
                $rule->setPriority(1);

                $em->persist($rule);

                $cpt++;
            }

        } else {
            $rule = new Rule();
            $rule->setCode('rule_one');
            $rule->setContent(json_encode([
                'conditions' => [
                    [
                        'field' => 'sku',
                        'operator' => '=',
                        'value' => '18383104'
                    ]
                ],
                'actions'    => [
                    [
                        'type' => 'set_value',
                        'field' => 'name',
                        'value' => 'My name'
                    ],
                    [
                        'type'        => 'copy_value',
                        'from_field'  => 'description',
                        'from_scope'  => 'ecommerce',
                        'from_locale' => 'en_US',
                        'to_field'    => 'description',
                        'to_scope'    => 'mobile',
                        'to_locale'   => 'en_US'
                    ],
                ]
            ]));
            $rule->setType('product');
            $rule->setPriority(10);
            $em->persist($rule);

            $rule = new Rule();
            $rule->setCode('rule_two');
            $rule->setContent(json_encode([
                'conditions' => [
                    [
                        'field' => 'name',
                        'operator' => '=',
                        'value' => 'My name'
                    ]
                ],
                'actions'    => [
                    [
                        'type' => 'set_value',
                        'field' => 'price',
                        'value' => [
                            ['data' => 44, 'currency' => 'EUR'],
                            ['data' => 72, 'currency' => 'USD'],
                        ]
                    ]
                ]
            ]));
            $rule->setType('product');
            $rule->setPriority(9);
            $em->persist($rule);

            $rule = new Rule();
            $rule->setCode('rule_four');
            $rule->setContent(json_encode([
                'conditions' => [
                    [
                        'field' => 'name',
                        'operator' => '=',
                        'value' => 'My name'
                    ]
                ],
                'actions'    => [
                    [
                        'type' => 'set_value',
                        'field' => 'optical_zoom',
                        'value' => 122.56,
                    ]
                ]
            ]));
            $rule->setType('product');
            $rule->setPriority(3);
            $em->persist($rule);

            $rule = new Rule();
            $rule->setCode('rule_five');
            $rule->setContent(json_encode([
                'conditions' => [
                    [
                        'field' => 'name',
                        'operator' => '=',
                        'value' => 'My name'
                    ]
                ],
                'actions'    => [
                    [
                        'type' => 'set_value',
                        'field' => 'image_stabilizer',
                        'value' => true,
                    ]
                ]
            ]));
            $rule->setType('product');
            $rule->setPriority(3);
            $em->persist($rule);

            // TODO image, option, options, metric
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
     * @param array   $filters
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
