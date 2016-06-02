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
                'export_time_strategy' => [
                    'css'        => '.updated-since-parameter .select2-container',
                    'decorators' => ['Pim\Behat\Decorator\Field\Select2Decorator']
                ]
            ],
            [
                'export_time_date' => [
                    'css'        => '.exported-since-date-wrapper input',
                    'decorators' => ['Pim\Behat\Decorator\Field\DatepickerDecorator']
                ]
            ],
            $this->elements
        );
    }
}
