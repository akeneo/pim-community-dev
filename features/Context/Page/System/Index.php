<?php

namespace Context\Page\System;

use Context\Page\Base\Form;

/**
 * System index page
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Index extends Form
{
    /**
     * @var string
     */
    protected $path = '#/system/';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            [
                'Locale field' => ['css' => 'system-locale'],
            ],
            $this->elements
        );
    }
}
