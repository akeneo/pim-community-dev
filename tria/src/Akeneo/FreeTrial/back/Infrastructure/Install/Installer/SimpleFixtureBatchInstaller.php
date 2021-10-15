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
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class SimpleFixtureBatchInstaller implements FixtureInstaller
{
    private FixtureReader $fixtureReader;

    private SimpleFactoryInterface $factory;

    private ObjectUpdaterInterface $updater;

    private ValidatorInterface $validator;

    private BulkSaverInterface $saver;

    private int $batchSize;

    private const DEFAULT_BATCH_SIZE = 100;

    public function __construct(
        FixtureReader $fixtureReader,
        SimpleFactoryInterface $factory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        BulkSaverInterface $saver,
        int $batchSize = self::DEFAULT_BATCH_SIZE
    ) {
        $this->fixtureReader = $fixtureReader;
        $this->factory = $factory;
        $this->updater = $updater;
        $this->validator = $validator;
        $this->saver = $saver;
        $this->batchSize = $batchSize;
    }

    public function install(): void
    {
        $fixturesBatch = [];
        foreach ($this->fixtureReader->read() as $fixtureData) {
            $fixture = $this->factory->create();
            $this->updater->update($fixture, $fixtureData);

            $violations = $this->validator->validate($fixture);
            if (0 !== $violations->count()) {
                throw new \Exception(sprintf(
                    'Validation failed on fixture "%s" with code "%s" and message: "%s"',
                    get_class($fixture),
                    $fixtureData['code'] ?? '',
                    iterator_to_array($violations)[0]->getMessage()
                ));
            }

            $fixturesBatch[] = $fixture;
            if (count($fixturesBatch) % $this->batchSize === 0) {
                $this->saver->saveAll($fixturesBatch);
                $fixturesBatch = [];
            }
        }

        if (!empty($fixturesBatch)) {
            $this->saver->saveAll($fixturesBatch);
        }
    }
}
