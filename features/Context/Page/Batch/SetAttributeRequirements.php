<?php

namespace Context\Page\Batch;

use Context\Page\Base\Wizard;

/**
 * Edit common attributes page
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetAttributeRequirements extends Wizard
{
    protected $elements = [
        'Available attributes button' => ['css' => 'button:contains("Select attributes")'],
        'Available attributes add button' => ['css' => '.pimmultiselect a:contains("Select")'],
        'Available attributes form' => ['css' => '#pim_catalog_mass_edit_family_add_attribute'],
        'Attributes' => ['css' => 'table.attributes'],
    ];

    /**
     * @param string $attribute
     * @param string $channel
     */
    public function switchAttributeRequirement($attribute, $channel)
    {
        $cell        = $this->getAttributeRequirementCell($attribute, $channel);
        $requirement = $cell->find('css', 'i');

        $requirement->click();
    }

    /**
     * @param string $attribute
     * @param string $channel
     *
     * @throws \Exception
     * @return NodeElement
     */
    private function getAttributeRequirementCell($attribute, $channel)
    {
        $attributesTable = $this->getElement('Attributes');
        $columnIdx = 0;

        foreach ($attributesTable->findAll('css', 'thead th') as $index => $header) {
            if ($header->getText() === strtoupper($channel)) {
                $columnIdx = $index;
                break;
            }
        }

        if (0 === $columnIdx) {
            throw new \Exception(sprintf('An error occured when trying to get the "%s" header', $channel));
        }

        $cells = $attributesTable->findAll('css', sprintf('tbody tr:contains("%s") td', $attribute));

        if (count($cells) < $columnIdx) {
            throw new \Exception(sprintf('An error occured when trying to get the attributes "%s" row', $attribute));
        }

        return $cells[$columnIdx];
    }
}
