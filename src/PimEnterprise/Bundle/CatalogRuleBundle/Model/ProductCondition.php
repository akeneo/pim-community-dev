<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Model;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Condition used in product rules.
 * A product condition is used to select products given a product field (or a product value), for an operator
 * a value criteria.
 *
 * For example: SKU CONTAINS '%foo%'
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class ProductCondition implements ProductConditionInterface
{
    /** @var string */
    protected $field;

    /** @var string */
    protected $operator;

    /** @var mixed */
    protected $value;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $optionsResolver = new OptionsResolver();
        $this->configureOptionsResolver($optionsResolver);

        //TODO: catch exception here and throw a real business exception ?
        $optionsResolver->resolve($data);

        $this->field = $data['field'];
        $this->operator = $data['operator'];
        $this->value = $data['value'];
    }

    /**
     * {@inheritdoc}
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * {@inheritdoc}
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Configure the condition's optionResolver
     *
     * @param OptionsResolver $optionsResolver
     */
    protected function configureOptionsResolver(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setRequired(['field', 'operator', 'value']);
        $optionsResolver->setAllowedTypes([
                'field' => 'string',
                'operator' => 'string'
            ]);
    }
}
