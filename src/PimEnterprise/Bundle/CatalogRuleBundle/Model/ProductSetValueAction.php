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
 * Set action used in product rules.
 * A set action value is used to set a product source field (or product value) with a given value
 * for a scope and a locale.
 *
 * For example : set description-fr_FR-ecommerce to 'foo'
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class ProductSetValueAction implements ProductSetValueActionInterface
{
    const TYPE = 'set_value';

    /** @var string */
    protected $field;

    /** @var mixed */
    protected $value;

    /** @var string */
    protected $locale;

    /** @var string */
    protected $scope;

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
        $this->value = $data['value'];
        $this->locale = $data['locale'];
        $this->scope = $data['scope'];
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Configure the condition's optionResolver
     *
     * @param OptionsResolver $optionsResolver
     */
    protected function configureOptionsResolver(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setDefaults(['locale' => null, 'scope'  => null]);
        $optionsResolver->setRequired(['field', 'value', 'type']);
        $optionsResolver->setAllowedValues(['type' => [self::TYPE]]);
        $optionsResolver->setAllowedTypes([
                'type' => 'string',
                'field' => 'string',
                'locale' => 'string',
                'scope' => 'string',
            ]);
    }
}
