<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Url;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Url as BaseUrl;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UrlSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Url::class);
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf(Constraint::class);
        $this->shouldBeAnInstanceOf(BaseUrl::class);
    }

    function it_provides_an_attribute_code()
    {
        $this->beConstructedWith(['attributeCode' => 'a_code']);

        $this->attributeCode->shouldBe('a_code');
    }

    function it_provides_empty_string_if_there_is_no_attribute_code()
    {
        $this->attributeCode->shouldBe('');
    }
}
