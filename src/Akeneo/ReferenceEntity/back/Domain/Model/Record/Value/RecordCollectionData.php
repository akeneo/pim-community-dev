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
class RecordCollectionData implements ValueDataInterface
{
    /** @var string[] */
    private $recordCodes;

    private function __construct(array $recordCodes)
    {
        Assert::notEmpty($recordCodes, 'Record codes should be a non empty array');

        $this->recordCodes = $recordCodes;
    }

    /**
     * @return string
     */
    public function normalize()
    {
        return $this->recordCodes;
    }

    public static function createFromNormalize($normalizedData): ValueDataInterface
    {
        Assert::allString($normalizedData, 'Each record codes should be a string');

        return new self($normalizedData);
    }

    public static function fromRecordCodes(array $recordCodes): RecordCollectionData
    {
        Assert::allIsInstanceOf(
            $recordCodes,
            RecordCode::class,
            sprintf('Each record codes should be an instance of "%s"', RecordCode::class)
        );

        $recordCodesString = array_map('strval', $recordCodes);

        return new self($recordCodesString);
    }
}
