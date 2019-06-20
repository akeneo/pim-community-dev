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
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindCodesByIdentifiersInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordConnectorValueTransformer implements ConnectorValueTransformerInterface
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

    public function transform(array $normalizedValue, AbstractAttribute $attribute): ?array
    {
        Assert::true($this->supports($attribute));

        $recordIdentifier = RecordIdentifier::fromString($normalizedValue['data']);
        $recordCodes = $this->findCodesByIdentifiers->find([$recordIdentifier]);

        if (empty($recordCodes)) {
            return null;
        }

        return [
            'locale'  => $normalizedValue['locale'],
            'channel' => $normalizedValue['channel'],
            'data'    => (string) current($recordCodes),
        ];
    }
}
