<?php

namespace Context\Page\Export;

use Context\Page\Base\Form;

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
    protected $path = '/spread/export/{id}/edit';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            [
                'Updated time condition' => [
                    'css'        => '.updated-since-parameter .controls',
                    'decorators' => ['Pim\Behat\Decorator\Export\Filter\UpdatedTimeConditionDecorator'],
                ],
                'Category tree' => [
                    'css'        => '.jstree',
                    'decorators' => ['Pim\Behat\Decorator\Tree\JsTreeDecorator']
                ]
            ],
            $this->elements
        );
    }
}
