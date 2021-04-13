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

namespace spec\Akeneo\ReferenceEntity\Application\Record\DeleteRecords;

use Akeneo\ReferenceEntity\Application\Record\DeleteRecords\DeleteRecordsCommand;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use PhpSpec\ObjectBehavior;

class DeleteRecordsHandlerSpec extends ObjectBehavior
{
    public function let(RecordRepositoryInterface $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_deletes_a_record_by_its_code_and_entity_identifier(RecordRepositoryInterface $repository)
    {
        $command = new DeleteRecordsCommand(
            'brand',
            ['brand_1', 'brand_2']
        );

        $recordFamilyIdentifier = ReferenceEntityIdentifier::fromString('brand');
        $recordCodes = [RecordCode::fromString('brand_1'), RecordCode::fromString('brand_2')];

        $repository->deleteByReferenceEntityAndCodes($recordFamilyIdentifier, $recordCodes)->shouldBeCalled();

        $this->__invoke($command);
    }
}
