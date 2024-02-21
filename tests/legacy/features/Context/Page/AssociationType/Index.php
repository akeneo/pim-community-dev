<?php

namespace Context\Page\AssociationType;

use Context\Page\Base\Grid;

/**
 * Association type index page
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Index extends Grid
{
    /**
     * @var string
     */
    protected $path = '#/configuration/association-type/';

    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            [
                'Creation link' => [
                    'css' => '#create-button-extension'
                ],
            ]
        );
    }
}
