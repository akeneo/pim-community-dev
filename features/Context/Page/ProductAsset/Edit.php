<?php

namespace Context\Page\ProductAsset;

use Context\Page\Base\Form;

/**
 * Asset edit page
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class Edit extends Form
{
    /** @var string */
    protected $path = '/enrich/asset/{id}/edit';

    /**
     * Fill a new date in the End of use at date picker
     *
     * @param string $date YEAR-MONTH-DAY e.g. 2015-06-20
     */
    public function changeTheEndOfUseAtTo($date)
    {
        $field = $this->find('css', 'label:contains("End of use at")');
        $this->fillDateField($field, $date);
    }
}
