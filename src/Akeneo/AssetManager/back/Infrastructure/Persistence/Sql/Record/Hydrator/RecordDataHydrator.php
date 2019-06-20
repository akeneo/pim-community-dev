<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\EmptyData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\RecordData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueDataInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindCodesByIdentifiersInterface;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordDataHydrator implements DataHydratorInterface
{
    /** @var FindCodesByIdentifiersInterface */
    private $findCodesByIdentifiers;

    public function __construct(FindCodesByIdentifiersInterface $findCodesByIdentifiers)
    {
        $this->findCodesByIdentifiers = $findCodesByIdentifiers;
    }

    public function supports(AbstractAttribute $attribute): bool
    {
        return $attribute instanceof RecordAttribute;
    }

    public function hydrate($normalizedData, AbstractAttribute $attribute): ValueDataInterface
    {
        $code = $this->findCode($normalizedData);
        if (null === $code) {
            return EmptyData::create();
        }

        return RecordData::createFromNormalize($code);
    }

    private function findCode(string $normalizedData): ?string
    {
        $results = $this->findCodesByIdentifiers->find([$normalizedData]);

        if (empty($results)) {
            return null;
        }

        return current($results);
    }
}
