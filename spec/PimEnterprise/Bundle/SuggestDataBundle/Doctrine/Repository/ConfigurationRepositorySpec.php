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

namespace spec\PimEnterprise\Bundle\SuggestDataBundle\Doctrine\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\SuggestDataBundle\Doctrine\Repository\ConfigurationRepository;
use PimEnterprise\Component\SuggestData\Model\Configuration;
use PimEnterprise\Component\SuggestData\Repository\ConfigurationRepositoryInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ConfigurationRepositorySpec extends ObjectBehavior
{
    function let(EntityManagerInterface $entityManager)
    {
        $this->beConstructedWith($entityManager, Configuration::class);
    }

    function it_is_configuration_repository()
    {
        $this->shouldHaveType(ConfigurationRepository::class);
        $this->shouldImplement(ConfigurationRepositoryInterface::class);
    }

    function it_finds_a_configuration_by_its_code($entityManager, ObjectRepository $repository)
    {
        $configuration = new Configuration('foobar', ['foo' => 'bar']);

        $entityManager->getRepository(Configuration::class)->willReturn($repository);
        $repository->findOneBy(['code' => 'foobar'])->willReturn($configuration);

        $this->find('foobar')->shouldReturn($configuration);
    }

    function it_finds_no_configuration_if_there_is_no_configuration_for_the_provided_code(
        $entityManager,
        ObjectRepository $repository
    ) {
        $entityManager->getRepository(Configuration::class)->willReturn($repository);
        $repository->findOneBy(['code' => 'foobar'])->willReturn(null);

        $this->find('foobar')->shouldReturn(null);
    }

    function it_saves_a_configuration($entityManager)
    {
        $configuration = new Configuration('foobar', ['foo' => 'bar']);

        $this->save($configuration);

        $entityManager->persist($configuration)->shouldHaveBeenCalled();
        $entityManager->flush()->shouldHaveBeenCalled();
    }
}
