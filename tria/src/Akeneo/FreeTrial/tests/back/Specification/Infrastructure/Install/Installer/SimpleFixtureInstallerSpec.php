<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\FreeTrial\Infrastructure\Install\Installer;

use Akeneo\FreeTrial\Infrastructure\Install\Reader\FixtureReader;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class SimpleFixtureInstallerSpec extends ObjectBehavior
{
    public function let(
        FixtureReader $fixtureReader,
        SimpleFactoryInterface $factory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        SaverInterface $saver
    ) {
        $this->beConstructedWith($fixtureReader, $factory, $updater, $validator, $saver);
    }

    public function it_successfully_installs_simple_fixtures(
        $fixtureReader,
        $factory,
        $updater,
        $validator,
        $saver
    ) {
        $fixtureData1 = ['code' => 'foo', 'label' => 'Foo'];
        $fixtureData2 = ['code' => 'bar', 'label' => 'Bar'];
        $fixtures = new \ArrayIterator([$fixtureData1, $fixtureData2]);

        $fixtureReader->read()->willReturn($fixtures);

        $fixtureObject1 = (new Attribute())->setCode('foo');
        $fixtureObject2 = (new Attribute())->setCode('bar');
        $factory->create()->willReturn($fixtureObject1, $fixtureObject2);

        $updater->update($fixtureObject1, $fixtureData1)->shouldBeCalled();
        $updater->update($fixtureObject2, $fixtureData2)->shouldBeCalled();

        $validator->validate($fixtureObject1)->willReturn(new ConstraintViolationList([]));
        $validator->validate($fixtureObject2)->willReturn(new ConstraintViolationList([]));

        $saver->save($fixtureObject1)->shouldBeCalled();
        $saver->save($fixtureObject2)->shouldBeCalled();

        $this->install();
    }

    public function it_throws_an_exception_if_a_fixture_is_invalid(
        $fixtureReader,
        $factory,
        $updater,
        $validator,
        $saver,
        ConstraintViolationInterface $violation
    ) {
        $fixtureData1 = ['code' => 'invalid', 'label' => 'Invalid fixture'];
        $fixtureData2 = ['code' => 'bar', 'label' => 'Bar'];
        $fixtures = new \ArrayIterator([$fixtureData1, $fixtureData2]);

        $fixtureReader->read()->willReturn($fixtures);
        $fixtureObject1 = (new Attribute())->setCode('foo');
        $factory->create()->willReturn($fixtureObject1);
        $updater->update($fixtureObject1, $fixtureData1)->shouldBeCalled();

        $validator->validate($fixtureObject1)->willReturn(new ConstraintViolationList([$violation->getWrappedObject()]));
        $violation->getMessage()->willReturn('Invalid data');

        $saver->save(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(\Exception::class)->during('install');
    }
}
