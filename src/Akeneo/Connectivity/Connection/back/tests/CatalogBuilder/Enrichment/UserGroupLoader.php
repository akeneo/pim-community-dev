<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment;

use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserGroupLoader
{
    private SimpleFactoryInterface $builder;
    private ObjectUpdaterInterface $updater;
    private SaverInterface $saver;
    private ValidatorInterface $validator;

    public function __construct(
        SimpleFactoryInterface $builder,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        SaverInterface $saver
    ) {
        $this->builder = $builder;
        $this->updater = $updater;
        $this->saver = $saver;
        $this->validator = $validator;
    }

    public function create(array $data = []): void
    {
        $group = $this->builder->create();
        $this->updater->update($group, $data);

        $violations = $this->validator->validate($group);
        if (0 !== $violations->count()) {
            throw new ViolationHttpException($violations);
        }

        $this->saver->save($group);
    }
}
