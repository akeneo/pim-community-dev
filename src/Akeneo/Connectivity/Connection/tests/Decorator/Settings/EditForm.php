<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Decorator\Settings;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditForm extends ElementDecorator
{
    use SpinCapableTrait;

    public function setLabel(string $label): void
    {
        $labelField = $this->spin(function () {
            return $this->element->find('css', 'input[name="label"]');
        }, 'Cannot find the label field');
        $labelField->setValue($label);
    }

    public function save(): void
    {
        $button = $this->spin(function () {
            return $this->element->find('css', '.AknButton--apply:not([disabled])');
        }, 'Cannot click on the save button.');

        $button->click();
    }
}
