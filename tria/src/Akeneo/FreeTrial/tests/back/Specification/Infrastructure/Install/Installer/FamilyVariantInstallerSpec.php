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
use Akeneo\Pim\Structure\Component\Model\FamilyVariant;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class FamilyVariantInstallerSpec extends ObjectBehavior
{
    public function let(
        FixtureReader $fixtureReader,
        SimpleFactoryInterface $factory,
        ObjectUpdaterInterface $updater,
        SaverInterface $saver,
        ValidatorInterface $validator
    ) {
        $this->beConstructedWith($fixtureReader, $factory, $updater, $saver, $validator);
    }

    public function it_successfully_installs_family_variants(
        $fixtureReader,
        $factory,
        $updater,
        $validator,
        $saver
    ) {
        $familyVariantData1 = ['code' => 'foo', 'label' => 'Foo', 'family' => 'ziggy'];
        $familyVariantData2 = ['code' => 'bar', 'label' => 'Bar', 'family' => 'another_family'];

        $fixtureReader->read()->willReturn(new \ArrayIterator([$familyVariantData1, $familyVariantData2]));

        $familyVariant1 = new FamilyVariant();
        $familyVariant1->setCode('foo');
        $familyVariant2 = new FamilyVariant();
        $familyVariant2->setCode('bar');

        $factory->create()->willReturn($familyVariant1, $familyVariant2);

        $updater->update($familyVariant1, ['code' => 'foo', 'label' => 'Foo'], ['familyCode' => 'ziggy'])->shouldBeCalled();
        $updater->update($familyVariant2, ['code' => 'bar', 'label' => 'Bar'], ['familyCode' => 'another_family'])->shouldBeCalled();

        $validator->validate($familyVariant1)->willReturn(new ConstraintViolationList([]));
        $validator->validate($familyVariant2)->willReturn(new ConstraintViolationList([]));

        $saver->save($familyVariant1)->shouldBeCalled();
        $saver->save($familyVariant2)->shouldBeCalled();

        $this->install();
    }

    public function it_throws_an_exception_if_a_family_variant_is_invalid(
        $fixtureReader,
        $factory,
        $updater,
        $validator,
        $saver,
        ConstraintViolationInterface $violation
    ) {
        $familyVariantData1 = ['code' => 'invalid', 'label' => 'Invalid', 'family' => 'ziggy'];
        $familyVariantData2 = ['code' => 'bar', 'label' => 'Bar', 'family' => 'another_family'];

        $fixtureReader->read()->willReturn(new \ArrayIterator([$familyVariantData1, $familyVariantData2]));

        $familyVariant1 = new FamilyVariant();
        $familyVariant1->setCode('foo');

        $factory->create()->willReturn($familyVariant1);

        $updater->update($familyVariant1, ['code' => 'invalid', 'label' => 'Invalid'], ['familyCode' => 'ziggy'])->shouldBeCalled();

        $validator->validate($familyVariant1)->willReturn(new ConstraintViolationList([$violation->getWrappedObject()]));
        $violation->getMessage()->willReturn('Invalid data');

        $saver->save(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(\Exception::class)->during('install');
    }
}
