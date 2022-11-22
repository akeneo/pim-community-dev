<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Decorator\Settings;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;
use Pim\Behat\Decorator\Field\Select2Decorator;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreationForm extends ElementDecorator
{
    use SpinCapableTrait;

    public function setLabel(string $label): void
    {
        $labelField = $this->spin(function () {
            return $this->element->find('css', 'input[name="label"]');
        }, 'Cannot find the label field');
        $labelField->setValue($label);
    }

    public function setFlowType(string $flowType): void
    {
        $flowTypeField = $this->spin(function () {
            return $this->element->find('css', '.select2-container.flowType');
        }, 'Cannot find the flow type field');

        $flowTypeField = $this->decorate(
            $flowTypeField,
            [Select2Decorator::class]
        );
        $flowTypeField->setValue($flowType);
    }

    public function save(): void
    {
        $this->spin(function () {
            $this->element->find('css', '.AknButton--apply')->click();

            return true;
        }, 'Cannot click on the save button.');
    }
}
