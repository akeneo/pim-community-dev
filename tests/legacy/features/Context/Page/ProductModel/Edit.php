<?php

namespace Context\Page\ProductModel;

use Context\Page\Base\ProductEditForm;
use Pim\Behat\Decorator\ContextSwitcherDecorator;

/**
 * Product model edit page
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Edit extends ProductEditForm
{
    /** @var string */
    protected $path = '#/enrich/product-model/{id}';

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
                    'css'        => '.AknTitleContainer-context',
                    'decorators' => [
                        ContextSwitcherDecorator::class
                    ]
                ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getHistoryRows()
    {
        return $this->findAll('css', '.entity-version');
    }
}
