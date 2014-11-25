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
 * Copy action used in product rules.
 * A copy action value is used to copy a product source value to a product target value.
 *
 * For example : description-fr_FR-ecommerce to description-fr_CH-tablet
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class ProductCopyValueAction implements ProductCopyValueActionInterface
{
    const TYPE = 'copy_value';

    /** @var string */
    protected $fromField;

    /** @var mixed */
    protected $fromLocale;

    /** @var string */
    protected $fromScope;

    /** @var string */
    protected $toField;

    /** @var mixed */
    protected $toLocale;

    /** @var string */
    protected $toScope;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $optionsResolver = new OptionsResolver();
        $this->configureOptionsResolver($optionsResolver);

        //TODO: catch exception here and throw a real business exception ?
        $optionsResolver->resolve($data);

        $this->fromField = $data['from_field'];
        $this->fromLocale = $data['from_locale'];
        $this->fromScope = $data['from_scope'];
        $this->toField = $data['to_field'];
        $this->toLocale = $data['to_locale'];
        $this->toScope = $data['to_scope'];
    }

    /**
     * {@inheritdoc}
     */
    public function getFromField()
    {
        return $this->fromField;
    }

    /**
     * {@inheritdoc}
     */
    public function getFromLocale()
    {
        return $this->fromLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function getFromScope()
    {
        return $this->fromScope;
    }

    /**
     * {@inheritdoc}
     */
    public function getToField()
    {
        return $this->toField;
    }

    /**
     * {@inheritdoc}
     */
    public function getToLocale()
    {
        return $this->toLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function getToScope()
    {
        return $this->toScope;
    }

    /**
     * Configure the condition's optionResolver
     *
     * @param OptionsResolver $optionsResolver
     */
    protected function configureOptionsResolver(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setDefaults([
                'from_locale' => null,
                'to_locale'   => null,
                'from_scope'  => null,
                'to_scope'    => null
            ]);
        $optionsResolver->setRequired(['from_field', 'to_field', 'type']);
        $optionsResolver->setAllowedValues(['type' => [self::TYPE]]);
        $optionsResolver->setAllowedTypes([
                'type' => 'string',
                'from_field' => 'string',
                'from_locale' => 'string',
                'to_field' => 'string',
                'to_locale' => 'string',
                'from_scope' => 'string',
                'to_scope' => 'string'
            ]);
    }
}
