<?php

namespace Context\Page\Batch;

use Context\Page\Base\Wizard;

/**
 * Edit common attributes page
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditCommonAttributes extends Wizard
{
    protected $elements = array(
        'Available attributes button'     => array('css' => 'button:contains("Select attributes")'),
        'Available attributes add button' => array('css' => '.pimmultiselect a:contains("Select")'),
        'Available attributes form'       => array(
            'css' => '#pim_enrich_mass_edit_action_operation_attributesToDisplay'
        ),
        'Locales dropdown' => array('css' => '#pim_enrich_mass_edit_action_operation_locale'),
    );

    /**
     * @param string $locale
     */
    public function switchLocale($locale)
    {
        $this->getElement('Locales dropdown')->selectOption($locale);
    }
}
