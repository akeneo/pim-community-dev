<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Domain\UserIntent\Factory;

use Akeneo\Category\Api\Command\UserIntents\SetRichText;
use Akeneo\Category\Api\Command\UserIntents\SetTextArea;
use Akeneo\Category\Domain\Model\Attribute\AttributeRichText;
use Akeneo\Category\Domain\Model\Attribute\AttributeTextArea;
use Akeneo\Category\Domain\Query\GetAttribute;
use Akeneo\Category\Domain\UserIntent\Factory\UserIntentFactory;
use Akeneo\Category\Domain\UserIntent\Factory\ValueUserIntentFactory;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsLocalizable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ValueUserIntentFactorySpec extends ObjectBehavior
{
    public function let(GetAttribute $getAttribute)
    {
        $this->beConstructedWith($getAttribute);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ValueUserIntentFactory::class);
        $this->shouldImplement(UserIntentFactory::class);
    }

    function it_manage_only_expected_field_names()
    {
        $this->getSupportedFieldNames()->shouldReturn(['values']);
    }

    function it_creates_a_list_of_value_intent_based_on_values_field(GetAttribute $getAttribute): void
    {
        $data = [
            'attribute_codes' => [
                'seo_meta_description|69e251b3-b876-48b5-9c09-92f54bfb528d',
                'description|840fcd1a-f66b-4f0c-9bbd-596629732950',
            ],
            'seo_meta_description|69e251b3-b876-48b5-9c09-92f54bfb528d|en_US' => [
                'data' => 'Meta shoes',
                'locale' => 'en_US',
                'attribute_code' => 'seo_meta_description|69e251b3-b876-48b5-9c09-92f54bfb528d'
            ],
            'description|840fcd1a-f66b-4f0c-9bbd-596629732950|en_US' => [
                'data' => 'Description',
                'locale' => 'en_US',
                'attribute_code' => 'description|840fcd1a-f66b-4f0c-9bbd-596629732950'
            ]
        ];

        $templateUuid = TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330');
        $valueCollection = AttributeCollection::fromArray([
            AttributeTextArea::create(
                AttributeUuid::fromString('69e251b3-b876-48b5-9c09-92f54bfb528d'),
                new AttributeCode('seo_meta_description'),
                AttributeOrder::fromInteger(4),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'SEO Meta Description']),
                $templateUuid
            ),
            AttributeRichText::create(
                AttributeUuid::fromString('840fcd1a-f66b-4f0c-9bbd-596629732950'),
                new AttributeCode('description'),
                AttributeOrder::fromInteger(1),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'Description']),
                $templateUuid
            )
        ]);

        $getAttribute->byIdentifiers($data['attribute_codes'])
            ->shouldBeCalledOnce()
            ->willReturn($valueCollection);

        $this->create(
            'values',
            $data
        )->shouldBeLike([
            new SetTextArea(
                'seo_meta_description|69e251b3-b876-48b5-9c09-92f54bfb528d',
                'en_US',
                'Meta shoes'
            ),
            new SetRichText(
                'description|840fcd1a-f66b-4f0c-9bbd-596629732950',
                'en_US',
                'Description'
            )
        ]);
    }
}
