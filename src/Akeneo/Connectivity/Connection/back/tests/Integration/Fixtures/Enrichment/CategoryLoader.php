<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\Enrichment;

use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryLoader
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
        $category = $this->builder->create();
        $this->updater->update($category, $data);

        $violations = $this->validator->validate($category);
        if (0 !== $violations->count()) {
            throw new ViolationHttpException($violations);
        }

        $this->saver->save($category);
    }
}
