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

namespace spec\Akeneo\Pim\Automation\SuggestData\Bundle\Doctrine\Repository;

use Akeneo\Pim\Automation\SuggestData\Bundle\Doctrine\Repository\ConfigurationRepository;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ConfigurationRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ConfigurationRepositorySpec extends ObjectBehavior
{
    public function let(EntityManagerInterface $entityManager)
    {
        $this->beConstructedWith($entityManager);
    }

    public function it_is_configuration_repository()
    {
        $this->shouldHaveType(ConfigurationRepository::class);
        $this->shouldImplement(ConfigurationRepositoryInterface::class);
    }
}
