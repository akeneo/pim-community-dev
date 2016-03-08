<?php

namespace Context\Page\Family;

use Context\Page\Base\Form;

/**
 * Family creation page
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Creation extends Form
{
    /** @var string */
    protected $path = '#/configuration/family/create';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            [
                'Save' => ['css' => '.ui-dialog-buttonset .btn-primary'],
            ]
        );
    }
}
