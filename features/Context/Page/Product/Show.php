<?php

namespace Context\Page\Product;

use Pim\Behat\Decorator\ContextSwitcherDecorator;

/**
 * Show product page
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class Show extends Edit
{
    /**
     * @var string
     */
    protected $path = '#/enrich/product/{id}';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            [
                'Main context selector' => [
                    'css'        => '.AknAttributeActions-contextSelectors',
                    'decorators' => [
                        ContextSwitcherDecorator::class
                    ]
                ]
            ]
        );
    }
}
