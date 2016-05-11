<?php

namespace Pim\Behat\Decorator\Field;

use Pim\Behat\Decorator\ElementDecorator;

class Select2Decorator extends ElementDecorator
{
    public function setValue($value)
    {
        $values            = explode(', ', $value);
        $autocompleteField = $this->find('css', '.select2-input');

        // TODO: handle choices deletion, see vendor/akeneo/pim-community-dev/features/Context/Page/Base/Form.php:709

        foreach ($values as $value) {
            $autocompleteField->setValue($value);
        }
    }
}
