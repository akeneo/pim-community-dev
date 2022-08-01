<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\SQL;

use Akeneo\Category\Application\Query\FindCategoryByIdentifier;
use Akeneo\Category\Domain\Model\Category;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlFindOneByIdentifierStub implements FindCategoryByIdentifier
{
    public function __invoke(int $identifier): ?Category
    {
        return new Category(
            new CategoryId($identifier),
            new Code('socks'),
            LabelCollection::fromArray(
                [
                    'en_US' => 'socks',
                    'fr_FR' => 'chaussettes',
                ]
            ),
            new CategoryId(1),
        );
    }
}
