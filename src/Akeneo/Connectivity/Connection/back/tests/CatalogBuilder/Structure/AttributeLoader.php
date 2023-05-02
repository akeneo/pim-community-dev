<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Structure;

use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeLoader
{
    public function __construct(private SimpleFactoryInterface $factory, private ObjectUpdaterInterface $updater, private SaverInterface $saver, private ValidatorInterface $validator)
    {
    }

    public function create(array $data): void
    {
        $data['group'] ??= AttributeGroupInterface::DEFAULT_CODE;

        $attribute = $this->factory->create();
        $this->updater->update($attribute, $data);

        $constraints = $this->validator->validate($attribute);
        Assert::assertCount(0, $constraints, 'The validation from the attribute creation failed.');

        $this->saver->save($attribute);
    }
}
