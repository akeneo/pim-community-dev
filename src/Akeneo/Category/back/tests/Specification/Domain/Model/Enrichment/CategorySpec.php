<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Domain\Model\Enrichment;

use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\TextValue;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\PermissionCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Category\Domain\ValueObject\ValueCollection;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CategorySpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(
            new CategoryId(1),
            new Code('my_category'),
            TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330'),
            LabelCollection::fromArray(['fr_FR' => 'category_libelle']),
            null,
            null,
            new CategoryId(1),
            \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2021-03-24 16:00:00'),
            ValueCollection::fromArray([TextValue::fromApplier(
                value: 'Meta shoes',
                uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
                code: 'seo_meta_description',
                channel: 'ecommerce',
                locale: 'en_US'
            )]),
            PermissionCollection::fromArray(
                [
                    "view" => [1 => "IT Support", 3 => "Redactor", 7 => "Manager"],
                    "edit" => [1 => "IT Support", 3 => "Redactor", 7 => "Manager"],
                    "own" => [1 => "IT Support", 3 => "Redactor", 7 => "Manager"],
                ]
            )
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Category::class);
    }

    function it_is_constructed_from_database_data()
    {
        $category = $this::fromDatabase([
            'id' => 1,
            'code' => 'my_category',
            'template_uuid' => '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'translations' => '{"fr_FR": "category_libelle"}',
            'root_id' => null,
            'lft' => 1,
            'rgt' => 1,
            'lvl' => 2,
            'parent_id' => 1,
            'updated' => '2021-03-24 16:00:00',
            'value_collection' => '{}',
            "permissions" => '{
                "view":{"1": "IT Support", "3": "Redactor", "7": "Manager"},
                "edit":{"1": "IT Support", "3": "Redactor", "7": "Manager"},
                "own":{"1": "IT Support", "3": "Redactor", "7": "Manager"}
            }',
        ]);

        $category->getId()->getValue()->shouldReturn(1);
        $category->getCode()->__toString()->shouldReturn('my_category');
        $category->getTemplateUuid()->__toString()->shouldReturn('02274dac-e99a-4e1d-8f9b-794d4c3ba330');
        $category->getLabels()->normalize()->shouldReturn(["fr_FR" => "category_libelle"]);
        $category->getRootId()->shouldReturn(null);
        $category->getParentId()->getValue()->shouldReturn(1);
        $category->getUpdated()->getTimestamp()->shouldReturn(1616601600);
        $category->getAttributes()->normalize()->shouldReturn([]);
        $category->getPermissions()->normalize()->shouldReturn([
            "view" => [1 => "IT Support", 3 => "Redactor", 7 => "Manager"],
            "edit" => [1 => "IT Support", 3 => "Redactor", 7 => "Manager"],
            "own" => [1 => "IT Support", 3 => "Redactor", 7 => "Manager"],
        ]);
    }

    function it_is_constructed_from_category_with_permissions()
    {
        $category = $this::fromDatabase([
            'id' => 1,
            'code' => 'my_category',
            'template_uuid' => '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'translations' => '{"fr_FR": "category_libelle"}',
            'root_id' => null,
            'lft' => 1,
            'rgt' => 1,
            'lvl' => 2,
            'parent_id' => 1,
            'updated' => '2021-03-24 16:00:00',
            'value_collection' => '{}',
            "permissions" => null,
        ]);

        $category->getPermissions()->normalize()->shouldReturn(null);

        $category = $this::fromCategoryWithPermissions(
            $category,
            [
                "view" => [1 => "IT Support", 3 => "Redactor", 7 => "Manager"],
                "edit" => [1 => "IT Support", 3 => "Redactor", 7 => "Manager"],
                "own" => [1 => "IT Support", 3 => "Redactor", 7 => "Manager"],
            ]
        );

        $category->getId()->getValue()->shouldReturn(1);
        $category->getCode()->__toString()->shouldReturn('my_category');
        $category->getTemplateUuid()->__toString()->shouldReturn('02274dac-e99a-4e1d-8f9b-794d4c3ba330');
        $category->getLabels()->normalize()->shouldReturn(["fr_FR" => "category_libelle"]);
        $category->getRootId()->shouldReturn(null);
        $category->getParentId()->getValue()->shouldReturn(1);
        $category->getUpdated()->getTimestamp()->shouldReturn(1616601600);
        $category->getAttributes()->normalize()->shouldReturn([]);
        $category->getPermissions()->normalize()->shouldReturn([
            "view" => [1 => "IT Support", 3 => "Redactor", 7 => "Manager"],
            "edit" => [1 => "IT Support", 3 => "Redactor", 7 => "Manager"],
            "own" => [1 => "IT Support", 3 => "Redactor", 7 => "Manager"],
        ]);
    }

    function it_is_set_with_null_label() {
        $this::setLabel('en_US', null);
        $this::getLabels()->getTranslation('en_US')->shouldReturn(null);
    }
}
