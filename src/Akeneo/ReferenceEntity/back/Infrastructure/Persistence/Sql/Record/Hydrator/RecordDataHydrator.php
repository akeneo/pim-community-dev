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
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\EmptyData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\RecordData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueDataInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordExistsInterface;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordDataHydrator implements DataHydratorInterface
{
    /** @var RecordExistsInterface */
    private $recordExists;

    public function __construct(RecordExistsInterface $recordExists)
    {
        $this->recordExists = $recordExists;
    }

    public function supports(AbstractAttribute $attribute): bool
    {
        return $attribute instanceof RecordAttribute;
    }

    public function hydrate($normalizedData, AbstractAttribute $attribute): ValueDataInterface
    {
        if (!$this->existsRecord($normalizedData, $attribute)) {
            return EmptyData::create();
        }

        return RecordData::createFromNormalize($normalizedData);
    }

    private function existsRecord(string $normalizedData, RecordAttribute $attribute): bool
    {
        return $this->recordExists->withReferenceEntityAndCode(
            $attribute->getRecordType(),
            RecordCode::fromString($normalizedData)
        );
    }
}
