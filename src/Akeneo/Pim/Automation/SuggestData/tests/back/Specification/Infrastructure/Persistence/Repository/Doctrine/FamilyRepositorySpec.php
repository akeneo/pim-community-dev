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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Repository\Doctrine;

use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Repository\FamilyRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Repository\Doctrine\FamilyRepository;
use Akeneo\Tool\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class FamilyRepositorySpec extends ObjectBehavior
{
    public function let(SearchableRepositoryInterface $familyRepository): void
    {
        $this->beConstructedWith($familyRepository);
    }

    public function it_is_an_attribute_mapping_family_repository(): void
    {
        $this->shouldImplement(FamilyRepositoryInterface::class);
    }

    public function it_is_the_doctrine_implementation_of_the_family_repository(): void
    {
        $this->shouldHaveType(FamilyRepository::class);
    }
}
