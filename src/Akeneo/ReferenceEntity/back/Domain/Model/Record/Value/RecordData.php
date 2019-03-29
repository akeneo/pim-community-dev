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

namespace Akeneo\ReferenceEntity\Domain\Model\Record\Value;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Webmozart\Assert\Assert;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class RecordData implements ValueDataInterface
{
    /** @var string */
    private $recordIdentifier;

    private function __construct(string $recordIdentifier)
    {
        Assert::notEmpty($recordIdentifier, 'Record identifier should be a non empty string');

        $this->recordIdentifier = $recordIdentifier;
    }

    /**
     * @return string
     */
    public function normalize()
    {
        return $this->recordIdentifier;
    }

    public static function createFromNormalize($normalizedData): ValueDataInterface
    {
        Assert::string($normalizedData, 'Normalized data should be a string');

        return new self($normalizedData);
    }

    public static function fromRecordCode(RecordCode $recordCode): RecordData
    {
        return new self((string) $recordCode);
    }
}
