<?php

declare(strict_types=1);

namespace Akeneo\Category\Application;

use Akeneo\Category\Api\Model\AttributeValues;
use Akeneo\Category\Api\Model\Category\Category;
use Akeneo\Category\Api\Model\CategoryReadModel;
use Akeneo\Category\Api\Model\Permissions;
use Akeneo\Category\Api\Query\GetCategoryQuery;
use Akeneo\Category\Domain\Model\Category as CategoryFromDomain;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;

/**
 * a GetCategoryHandler executes GetCategoryQuery queries
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoryHandler
{
    public function __invoke(GetCategoryQuery $query): CategoryReadModel
    {
        $code = new Code('clothes');
        $labels = LabelCollection::fromArray([
            "fr_FR" => "Vêtements",
            "en_US" => "Clothes",
            "de_DE" => "Kleidung"
        ]);

        // the category as used internally
        $domainCategory = new CategoryFromDomain($query->categoryId(), $code, $labels, new CategoryId(1));

        /* @var $permission Permissions */
        $permissions = new Permissions([
            'view' => [1],
            'edit' => [1, 2],
            'own' => [1, 2, 3]
        ]);


        /* @var $attributeValues AttributeValues */
        $attributeValues = new AttributeValues([
            "description_87939c45-1d85-4134-9579-d594fff65030_en_US" => [
                "data" => "All the shoes you need!",
                "locale" => "en_US"
            ],
            "description_87939c45-1d85-4134-9579-d594fff65030_fr_FR" => [
                "data" => "Les chaussures dont vous avez besoin !",
                "locale" => "fr_FR"
            ],
            "banner_8587cda6-58c8-47fa-9278-033e1d8c735c" => [
                "data" => [
                    "size" => 168107,
                    "file_path" => "8/8/3/d/883d041fc9f22ce42fee07d96c05b0b7ec7e66de_shoes.jpg",
                    "mime_type" => "image/jpeg",
                    "extension" => "jpg",
                    "original_filename" => "shoes.jpg"
                ],
                "locale" => null,
            ],
            "seo_meta_title_ebdf744c-17e0-11ed-835e-0b2d6a7798db" => [
                "data" => "Shoes at will",
                "locale" => null
            ],
            "seo_meta-description_ef7ace80-17e0-11ed-9ac6-2feec2ba2321_en_US" => [
                "data" => "At cheapshoes we have tons of shoes for everyone\nYou dream of a shoe, we have it.",
                "locale" => "en_US"
            ],// no fr_FR
            "seo_keywords_54f6725a-17e1-11ed-a002-73412755f3bd_en_US" => [
                "data" => "Shoes Slippers Sneakers",
                "locale"=>"en_US"
            ],
            "seo_keywords_54f6725a-17e1-11ed-a002-73412755f3bd_fr_FR" => [
                "data" => "Chaussures Tongues Espadrilles",
                "locale"=>"fr_FR"
            ]
        ]);

        $readModel = new CategoryReadModel(
            Category::fromDomainModel($domainCategory),
            $permissions,
            $attributeValues
        );
        // returning the category as seen by the outside (whoever created and dispatched the query)
        return $readModel;
    }

}
