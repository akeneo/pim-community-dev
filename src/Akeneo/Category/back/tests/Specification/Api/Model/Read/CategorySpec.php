<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Api\Model\Read;

use Akeneo\Category\Api\Model\Read\Category;
use Akeneo\Category\Domain\Model\Category as CategoryDomain;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\PermissionCollection;
use Akeneo\Category\Domain\ValueObject\ValueCollection;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CategorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Category::class);
    }

    function it_normalizes_category_from_domain(): void
    {
        $this->beConstructedThrough(
            'fromDomain',
            [$this->givenCategory()]
        );

        $expectedNormalizedCategory = [
            'id' => 1,
            'code' => 'code',
            'parent' => 2,
            'labels' => ['fr_FR' => 'Vêtements'],
            'attributes' => [
                'description_87939c45-1d85-4134-9579-d594fff65030_en_US' => [
                    'data' => 'All the shoes you need!',
                    'locale' => 'en_US',
                ],
                'description_87939c45-1d85-4134-9579-d594fff65030_fr_FR' => [
                    'data' => 'Les chaussures dont vous avez besoin !',
                    'locale' => 'fr_FR',
                ]
            ],
            'permissions' => [
                'view' => [1],
                'edit' => [1, 2],
                'own' => [1, 2, 3],
            ],
        ];
        $this->normalize()->shouldReturn($expectedNormalizedCategory);
    }

    private function givenCategory(): CategoryDomain
    {
        return new CategoryDomain(
            id: new CategoryId(1),
            code: new Code('code'),
            labelCollection: LabelCollection::fromArray(['fr_FR' => 'Vêtements']),
            parentId: new CategoryId(2),
            valueCollection: ValueCollection::fromArray([
                'description_87939c45-1d85-4134-9579-d594fff65030_en_US' => [
                    'data' => 'All the shoes you need!',
                    'locale' => 'en_US',
                ],
                'description_87939c45-1d85-4134-9579-d594fff65030_fr_FR' => [
                    'data' => 'Les chaussures dont vous avez besoin !',
                    'locale' => 'fr_FR',
                ]
            ]),
            permissionCollection: PermissionCollection::fromArray([
                'view' => [1],
                'edit' => [1, 2],
                'own' => [1, 2, 3],
            ]),
        );
    }

}
