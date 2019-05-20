<?php

namespace Context\Page\Export;

use Context\Page\Base\Form;
use Pim\Behat\Decorator\Export\Structure\AttributesDecorator;
use Pim\Behat\Decorator\Tree\JsTreeDecorator;

/**
 * Export edit page
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Edit extends Form
{
    /**
     * @var string
     */
    protected $path = '#/spread/export/{code}/edit';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            [
                'Category tree' => [
                    'css'        => '.jstree',
                    'decorators' => [JsTreeDecorator::class]
                ],
                'Attribute selector' => [
                    'css'        => '.AknFieldContainer.attributes',
                    'decorators' => [AttributesDecorator::class]
                ]
            ],
            $this->elements
        );
    }
}
