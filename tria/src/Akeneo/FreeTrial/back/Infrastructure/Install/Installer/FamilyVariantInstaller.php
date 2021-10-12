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

namespace Akeneo\FreeTrial\Infrastructure\Install\Installer;

use Akeneo\FreeTrial\Infrastructure\Install\Reader\FixtureReader;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class FamilyVariantInstaller implements FixtureInstaller
{
    private FixtureReader $fixtureReader;

    private SimpleFactoryInterface $factory;

    private ObjectUpdaterInterface $updater;

    private SaverInterface $saver;

    private ValidatorInterface $validator;

    public function __construct(
        FixtureReader $fixtureReader,
        SimpleFactoryInterface $factory,
        ObjectUpdaterInterface $updater,
        SaverInterface $saver,
        ValidatorInterface $validator
    ) {
        $this->fixtureReader = $fixtureReader;
        $this->factory = $factory;
        $this->updater = $updater;
        $this->saver = $saver;
        $this->validator = $validator;
    }

    public function install(): void
    {
        foreach ($this->fixtureReader->read() as $familyVariantData) {
            $familyCode = $familyVariantData['family'];
            unset($familyVariantData['family']);

            $familyVariant = $this->factory->create();
            $this->updater->update($familyVariant, $familyVariantData, ['familyCode' => $familyCode]);

            $violations = $this->validator->validate($familyVariant);
            if (0 !== $violations->count()) {
                throw new \Exception(sprintf(
                    'Validation failed on family variant "%s" with message: "%s"',
                    $familyVariant['code'],
                    iterator_to_array($violations)[0]->getMessage()
                ));
            }

            $this->saver->save($familyVariant);
        }
    }
}
