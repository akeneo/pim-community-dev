<?php

namespace Context\Page\Batch;

use Context\Page\Base\Wizard;

/**
 * Edit common attributes page
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditCommonAttributes extends Wizard
{
    protected $elements = array(
        'Available attributes button'     => array('css' => 'button:contains("Select attributes")'),
        'Available attributes add button' => array('css' => '.pimmultiselect a:contains("Select")'),
    );

    public function fillField($field, $value)
    {
        $label = $this->find('css', sprintf('#pim_catalog_mass_edit_action label:contains("%s")', $field));

        if (null === $label) {
            throw new \InvalidArgumentException(sprintf('Impossible to find field %s', $field));
        }

        if ($label->hasAttribute('for')) {
            $field = $this->find('css', sprintf('#%s', $label->getAttribute('for')));
            $field->setValue($value);
        }
    }
}
