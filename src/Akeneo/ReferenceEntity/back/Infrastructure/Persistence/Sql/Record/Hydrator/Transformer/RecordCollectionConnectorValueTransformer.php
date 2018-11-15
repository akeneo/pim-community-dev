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

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\Transformer;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindExistingRecordCodesInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordCollectionConnectorValueTransformer implements ConnectorValueTransformerInterface
{
    /** @var FindExistingRecordCodesInterface */
    private $findExistingRecordCodes;

    public function __construct(FindExistingRecordCodesInterface $findExistingRecordCodes)
    {
        $this->findExistingRecordCodes = $findExistingRecordCodes;
    }

    public function supports(AbstractAttribute $attribute): bool
    {
        return $attribute instanceof RecordCollectionAttribute;
    }

    public function transform(array $normalizedValue, AbstractAttribute $attribute): ?array
    {
        Assert::true($this->supports($attribute));

        $referenceEntityIdentifier = $attribute->getRecordType();
        $existingRecordCodes = ($this->findExistingRecordCodes)($referenceEntityIdentifier, $normalizedValue['data']);

        if (empty($existingRecordCodes)) {
            return null;
        }

        return [
            'locale'  => $normalizedValue['locale'],
            'channel' => $normalizedValue['channel'],
            'data'    => $existingRecordCodes,
        ];
    }
}
