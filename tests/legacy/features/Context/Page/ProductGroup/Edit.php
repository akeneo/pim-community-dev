<?php

namespace Context\Page\ProductGroup;

use Context\Page\Base\Form;
use Pim\Behat\Decorator\ReactContextSwitcherDecorator;

/**
 * Group edit page
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Edit extends Form
{
    /**
     * @var string
     */
    protected $path = '#/enrich/group/{code}/edit';

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
                    'css'        => '.tab-container .object-attributes .attribute-edit-actions',
                    'decorators' => [
                        ReactContextSwitcherDecorator::class
                    ]
                ],
                'Save' => ['css' => 'button.save'],
            ]
        );
    }
}
