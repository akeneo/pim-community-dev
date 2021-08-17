<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredExport\Infrastructure\Query\ReferenceEntity;

use Akeneo\Platform\TailoredExport\Infrastructure\Query\ReferenceEntity\FindRecordLabels;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Enrich\FindRecordsLabelTranslationsInterface;
use PhpSpec\ObjectBehavior;

class FindRecordLabelsSpec extends ObjectBehavior
{
    public function let(
        FindRecordsLabelTranslationsInterface $findRecordLabelTranslations
    ): void {
        $this->beConstructedWith($findRecordLabelTranslations);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(FindRecordLabels::class);
    }

    public function it_finds_the_label_of_records(
        FindRecordsLabelTranslationsInterface $findRecordLabelTranslations
    ): void {
        $referenceEntityCode = 'designer';
        $recordCodes = ['stark', 'ron'];
        $localeCode = 'fr_FR';
        $expectedLabels = ['stark' => 'Philippe Stark', 'ron' => 'Ron Arad'];

        $findRecordLabelTranslations->find($referenceEntityCode, $recordCodes, $localeCode)
            ->willReturn($expectedLabels);

        $this->byReferenceEntityCodeAndRecordCodes($referenceEntityCode, $recordCodes, $localeCode)
            ->shouldReturn($expectedLabels);
    }

    public function it_returns_null_when_the_label_is_empty(
        FindRecordsLabelTranslationsInterface $findRecordLabelTranslations
    ): void {
        $referenceEntityCode = 'designer';
        $recordCodes = ['stark', 'ron'];
        $localeCode = 'fr_FR';
        $expectedLabels = ['james' => null];

        $findRecordLabelTranslations->find($referenceEntityCode, $recordCodes, $localeCode)
            ->willReturn($expectedLabels);

        $this->byReferenceEntityCodeAndRecordCodes($referenceEntityCode, $recordCodes, $localeCode)
            ->shouldReturn($expectedLabels);
    }
}
