<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value;

use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class TextAreaValueFactory extends ScalarValueFactory implements ReadValueFactory
{
    public function supportedAttributeType(): string
    {
        return AttributeTypes::TEXTAREA;
    }
}
