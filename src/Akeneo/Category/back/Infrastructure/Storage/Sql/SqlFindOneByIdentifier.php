<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\FindCategoryByIdentifier;
use Akeneo\Category\Domain\Model\Category;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\ValueCollection;
use Akeneo\Category\Domain\ValueObject\PermissionCollection;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlFindOneByIdentifier implements FindCategoryByIdentifier
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function __invoke(int $identifier): ?Category
    {
        $query = <<<SQL
SELECT cat.id, cat.code, cat.parent_id, JSON_ARRAYAGG(JSON_OBJECT(
    trans.locale,
    trans.label
    )) AS labels
FROM pim_catalog_category AS cat
JOIN pim_catalog_category_translation AS trans
ON trans.foreign_key = cat.id
AND cat.id = :id
SQL;
        $statement = $this->connection->executeQuery($query, ['id' => $identifier]);
        $row = $statement->fetchAssociative();
        if (false === $row) {
            return null;
        }

        $labelCollection = [];
        /**
         * Before:
         * [
         *      [0] => ['en_US' => 'socks'],
         *      [1] => ['fr_FR' => 'chaussettes'],
         * ]
         * After:
         * [
         *     ['en_US' => 'socks'],
         *     ['fr_FR' => 'chaussettes'],
         * ]
         */
        array_map(static function ($label) use (&$labelCollection) {
            $labelCollection[array_keys($label)[0]] = array_values($label)[0];
        }, json_decode($row['labels'], true, 512, JSON_THROW_ON_ERROR));

        $attributes = ValueCollection::fromArray([
'banner_8587cda6-58c8-47fa-9278-033e1d8c735c' => [
    'data' => [
        'extension'=> 'jpg',
        'file_path'=> '8/8/3/d/883d041fc9f22ce42fee07d96c05b0b7ec7e66de_shoes.jpg',
        'mime_type'=> 'image/jpeg',
        'original_filename'=> 'shoes.jpg',
        'size'=> 168107
    ],
    'locale'=>null
],
'description_87939c45-1d85-4134-9579-d594fff65030_en_US'=> ['data'=> 'All the shoes you need!', "locale"=> 'en_US'],
'description_87939c45-1d85-4134-9579-d594fff65030_fr_FR'=> ['data'=> 'Les chaussures dont vous avez besoin !', 'locale'=> 'fr_FR'],
'seo_keywords_54f6725a-17e1-11ed-a002-73412755f3bd_en_US'=> ['data'=> 'Shoes Slippers Sneakers', 'locale'=> 'en_US'],
'seo_keywords_54f6725a-17e1-11ed-a002-73412755f3bd_fr_FR'=> ['data'=> 'Chaussures Tongues Espadrilles', 'locale'=> 'fr_FR'],
'seo_meta-description_ef7ace80-17e0-11ed-9ac6-2feec2ba2321_en_US'=> ['data'=> 'At cheapshoes we have tons of shoes for everyone\nYou dream of a shoe, we have it.', 'locale'=> 'en_US'],
'seo_meta_title_ebdf744c-17e0-11ed-835e-0b2d6a7798db'=> ['data'=>'Shoes at will', 'locale'=> null]
        ]);

        $permissions = PermissionCollection::fromArray([
            'view' => [1, 2, 3],
            'edit' => [1, 2],
            'own' => [1],
        ]);

        return new Category(
            new CategoryId((int)$row['id']),
            new Code($row['code']),
            LabelCollection::fromArray($labelCollection),
            $row['parent_id'] ? new CategoryId((int)$row['parent_id']) : null,
            $attributes,
            $permissions
        );
    }
}
