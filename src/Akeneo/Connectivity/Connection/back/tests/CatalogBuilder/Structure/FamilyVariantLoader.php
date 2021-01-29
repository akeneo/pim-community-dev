<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Structure;

use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyVariantLoader
{
    /** @var SimpleFactoryInterface */
    private $factory;

    /** @var ObjectUpdaterInterface */
    private $updater;

    /** @var ValidatorInterface */
    private $validator;

    /** @var SaverInterface */
    private $saver;

    public function __construct(
        SimpleFactoryInterface $factory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        SaverInterface $saver
    ) {
        $this->factory = $factory;
        $this->updater = $updater;
        $this->validator = $validator;
        $this->saver = $saver;
    }

    /**
     * @throws \Exception
     */
    public function create(array $data = []): FamilyVariantInterface
    {
        /** @var  FamilyVariantInterface $familyVariant */
        $familyVariant = $this->factory->create();

        $this->updater->update($familyVariant, $data);
        $errors = $this->validator->validate($familyVariant);

        if (0 !== $errors->count()) {
            throw new \Exception(
                sprintf(
                    'Impossible to setup test in %s: %s',
                    static::class,
                    $errors->get(0)->getMessage()
                )
            );
        }

        $this->saver->save($familyVariant);

        return $familyVariant;
    }
}
