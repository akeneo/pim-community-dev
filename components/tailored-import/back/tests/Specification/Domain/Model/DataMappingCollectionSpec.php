<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\TailoredImport\Domain\Model;

use Akeneo\Platform\TailoredImport\Domain\Model\DataMapping;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\TargetAttribute;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DataMappingCollectionSpec extends ObjectBehavior
{
    public function it_can_be_initialized()
    {
        $this->beConstructedThrough('create', [
            [
                DataMapping::create(
                    'b244c45c-d5ec-4993-8cff-7ccd04e82fef',
                    TargetAttribute::create(
                        'code',
                        'pim_catalog_text',
                        'web',
                        'fr_FR',
                        'set',
                        'skip',
                        null,
                    ),
                    [],
                    OperationCollection::create([]),
                    [],
                ),
            ],
        ]);

        $this->getIterator()->shouldHaveCount(1);
    }

    public function it_cannot_be_initialized_without_data_mapping()
    {
        $this->beConstructedThrough('create', [[]]);
        $this->shouldThrow(new \InvalidArgumentException('Expected a non-empty value. Got: array'))->duringInstantiation();
    }
}
