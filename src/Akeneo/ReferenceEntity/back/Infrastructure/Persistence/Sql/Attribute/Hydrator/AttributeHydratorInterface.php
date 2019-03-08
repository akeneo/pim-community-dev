<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 * @api
 */
interface AttributeHydratorInterface
{
    public function supports(array $result): bool;

    public function hydrate(array $row): AbstractAttribute;
}
