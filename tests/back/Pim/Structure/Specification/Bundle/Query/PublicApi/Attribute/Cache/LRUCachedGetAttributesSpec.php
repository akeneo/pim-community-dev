<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Bundle\Query\PublicApi\Attribute\Cache;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LRUCachedGetAttributesSpec extends ObjectBehavior
{
    public function let(GetAttributes $getAttributes) {
        $this->beConstructedWith($getAttributes);
    }

    public function it_gets_attributes_by_doing_a_query_if_the_cache_is_not_hit(GetAttributes $getAttributes)
    {
        $aText = new Attribute('a_text', AttributeTypes::TEXT, [], false, false, null, false, 'text', []);
        $aTextarea = new Attribute('a_textarea', AttributeTypes::TEXTAREA, [], false, false, null, false, 'textarea', []);
        $aBoolean = new Attribute('a_boolean', AttributeTypes::BOOLEAN, [], false, false, null, false, 'boolean', []);
        $getAttributes->forCodes(['a_text', 'a_textarea', 'a_boolean'])->willReturn(['a_text' => $aText, 'a_textarea' => $aTextarea, 'a_boolean' => $aBoolean]);
        $this->forCodes(['a_text', 'a_textarea', 'a_boolean'])->shouldReturn(['a_text' => $aText, 'a_textarea' => $aTextarea, 'a_boolean' => $aBoolean]);
    }

    public function it_gets_attributes_from_the_cache_when_the_cache_is_hit(GetAttributes $getAttributes) {
        $aText = new Attribute('a_text', AttributeTypes::TEXT, [], false, false, null, false, 'text', []);
        $aTextarea = new Attribute('a_textarea', AttributeTypes::TEXTAREA, [], false, false, null, false, 'textarea', []);
        $aBoolean = new Attribute('a_boolean', AttributeTypes::BOOLEAN, [], false, false, null, false, 'boolean', []);
        $getAttributes->forCodes(['a_text', 'a_textarea', 'a_boolean', 'michel'])->willReturn(['a_text' => $aText, 'a_textarea' => $aTextarea, 'a_boolean' => $aBoolean, 'michel' => null]);
        $getAttributes->forCodes(['a_text', 'a_textarea', 'a_boolean', 'michel'])->shouldBeCalled(1);
        $getAttributes->forCodes([])->willReturn([]);

        $this->forCodes(['a_text', 'a_textarea', 'a_boolean', 'michel'])->shouldReturn(['a_text' => $aText, 'a_textarea' => $aTextarea, 'a_boolean' => $aBoolean, 'michel' => null]);
        $this->forCodes(['a_text', 'a_textarea', 'a_boolean', 'michel'])->shouldReturn(['a_text' => $aText, 'a_textarea' => $aTextarea, 'a_boolean' => $aBoolean, 'michel' => null]);
    }

    public function it_mixes_the_call_between_the_cache_and_the_non_cached(GetAttributes $getAttributes) {
        $aText = new Attribute('a_text', AttributeTypes::TEXT, [], false, false, null, false, 'text', []);
        $aTextarea = new Attribute('a_textarea', AttributeTypes::TEXTAREA, [], false, false, null, false, 'textarea', []);
        $aBoolean = new Attribute('a_boolean', AttributeTypes::BOOLEAN, [], false, false, null, false, 'boolean', []);
        $getAttributes->forCodes(['a_text', 'a_textarea', 'a_boolean'])->willReturn(['a_text' => $aText, 'a_textarea' => $aTextarea, 'a_boolean' => $aBoolean]);
        $getAttributes->forCodes(['michel'])->willReturn(['michel' => null]);

        $this->forCodes(['a_text', 'a_textarea', 'a_boolean'])->shouldReturn(['a_text' => $aText, 'a_textarea' => $aTextarea, 'a_boolean' => $aBoolean]);
        $this->forCodes(['a_text', 'a_textarea', 'a_boolean', 'michel'])->shouldReturn(['michel' => null, 'a_text' => $aText, 'a_textarea' => $aTextarea, 'a_boolean' => $aBoolean]);
    }

    public function it_can_gets_more_than_the_cache_size(GetAttributes $getAttributes) {
        $attributes = [];
        for ($i = 0; $i < 1500; $i++) {
            $attributeCode = "an_attribute_$i";
            $attributes[$attributeCode] = new Attribute($attributeCode, AttributeTypes::TEXT, [], false, false, null, false, 'text', []);
        }

        $getAttributes->forCodes(array_keys($attributes))->willReturn(array_values($attributes));
        $this->forCodes(array_keys($attributes))->shouldReturn(array_values($attributes));
    }
}
