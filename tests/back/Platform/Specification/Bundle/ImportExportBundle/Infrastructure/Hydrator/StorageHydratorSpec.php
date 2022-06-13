<?php

declare(strict_types=1);

/*
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Specification\Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Hydrator;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\NoneStorage;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\StorageHydratorInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class StorageHydratorSpec extends ObjectBehavior
{
    public function let(
        StorageHydratorInterface $localHydrator,
        StorageHydratorInterface $noneHydrator,
    ) {
        $localHydrator->supports(Argument::any())->willReturn(false);
        $localHydrator->supports(['type' => 'local'])->willReturn(true);
        $noneHydrator->supports(Argument::any())->willReturn(false);
        $noneHydrator->supports(['type' => 'none'])->willReturn(true);

        $this->beConstructedWith([
            $noneHydrator,
            $localHydrator,
        ]);
    }

    public function it_supports_hydration_when_an_hydrator_support_hydration()
    {
        $this->supports(['type' => 'none'])->shouldReturn(true);
        $this->supports(['type' => 'unknown'])->shouldReturn(false);
    }

    public function it_hydrates_with_the_first_supported_hydrator(StorageHydratorInterface $noneHydrator)
    {
        $noneHydrator->hydrate(['type' => 'none'])->willReturn(new NoneStorage());

        $this->hydrate(['type' => 'none'])->shouldBeLike(new NoneStorage());
    }

    public function it_throws_an_exception_when_no_hydrator_supports_hydration()
    {
        $this->shouldThrow(\LogicException::class)->during('hydrate', [['type' => 'unknown']]);
    }
}
