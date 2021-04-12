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

namespace spec\Akeneo\ReferenceEntity\Application\Record\MassDeleteRecords;

use Akeneo\ReferenceEntity\Application\Record\MassDeleteRecords\MassDeleteRecordsCommand;
use Akeneo\ReferenceEntity\Application\Record\MassDeleteRecords\MassDeleteRecordsLauncherInterface;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use PhpSpec\ObjectBehavior;

class MassDeleteRecordsHandlerSpec extends ObjectBehavior
{
    function let(MassDeleteRecordsLauncherInterface $massDeleteRecordsLauncher)
    {
        $this->beConstructedWith($massDeleteRecordsLauncher);
    }

    function is_launch_a_job(MassDeleteRecordsLauncherInterface $massDeleteRecordsLauncher)
    {
        $normalizedQuery = [
            "page" => 0,
            "size" => 50,
            "locale" => "en_US",
            "channel" => "ecommerce",
            "filters" => [
                [
                    "field" => "reference_entity",
                    "value" => "brand",
                    "context" => [],
                    "operator" => "="
                ],
                [
                    "field" => "code",
                    "value" => ["brand_1"],
                    "context" => [],
                    "operator" => "IN"
                ],
            ]
        ];

        $editRecordCommand = new MassDeleteRecordsCommand('brand', $normalizedQuery);

        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('brand');
        $query = RecordQuery::createFromNormalized($normalizedQuery);

        $massDeleteRecordsLauncher
            ->launchForReferenceEntityAndQuery($referenceEntityIdentifier, $query)
            ->shouldBeCalled();

        $this->__invoke($editRecordCommand);
    }
}
