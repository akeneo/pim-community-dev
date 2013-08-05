<?php

namespace Context\Page\Locale;

use Context\Page\Base\Form;

/**
 * Behat context page for locale creation
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Creation extends Form
{
    protected $path = '/configuration/locale/create';

    protected $elements = array(
        'page'      => array('css' => 'body'),
        'container' => array('css' => 'div[id=container]')
    );
}
