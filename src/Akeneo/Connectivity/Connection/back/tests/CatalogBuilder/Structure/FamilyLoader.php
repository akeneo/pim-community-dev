<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Structure;

use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FamilyLoader
{
    public function __construct(
        private SimpleFactoryInterface $factory,
        private ObjectUpdaterInterface $updater,
        private SaverInterface $saver,
        private ValidatorInterface $validator
    ) {
    }

    public function create(array $data): void
    {
        $family = $this->factory->create();
        $data['attributes'] = \array_merge(['sku'], $data['attributes']);
        $this->updater->update($family, $data);

        $constraints = $this->validator->validate($family);
        Assert::assertCount(0, $constraints, 'The validation from the family creation failed.');

        $this->saver->save($family);
    }
}
