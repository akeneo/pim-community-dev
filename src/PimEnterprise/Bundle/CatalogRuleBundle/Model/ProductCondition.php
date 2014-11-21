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

use PimEnterprise\Bundle\RuleEngineBundle\Model\ConditionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Condition used in product rules.
 */
class ProductCondition implements ConditionInterface
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
        $this->configureCondition($optionsResolver);

        //TODO: catch exception here and throw a real business exception ?
        $optionsResolver->resolve($data);

        $this->field = $data['field'];
        $this->operator = $data['operator'];
        $this->value = $data['value'];
    }

    /**
     * Configure the condition's optionResolver
     *
     * @param OptionsResolver $optionsResolver
     */
    protected function configureCondition(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setRequired(['field', 'operator', 'value']);
        $optionsResolver->setAllowedTypes([
                'field' => 'string',
                'operator' => 'string'
            ]);
    }
}
