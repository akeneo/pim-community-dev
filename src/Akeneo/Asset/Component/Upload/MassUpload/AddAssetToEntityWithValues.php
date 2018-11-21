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

namespace Akeneo\Asset\Component\Upload\MassUpload;

use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Adds asset to a product or a product model, for a given attribute of type asset collection.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class AddAssetToEntityWithValues
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $entityWithValueRepository;

    /** @var ObjectUpdaterInterface */
    protected $entityWithValueUpdater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var SaverInterface */
    protected $entityWithValueSaver;

    /**
     * @param IdentifiableObjectRepositoryInterface $entityWithValueRepository
     * @param ObjectUpdaterInterface                $entityWithValueUpdater
     * @param ValidatorInterface                    $validator
     * @param SaverInterface                        $entityWithValueSaver
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $entityWithValueRepository,
        ObjectUpdaterInterface $entityWithValueUpdater,
        ValidatorInterface $validator,
        SaverInterface $entityWithValueSaver
    ) {
        $this->entityWithValueRepository = $entityWithValueRepository;
        $this->entityWithValueUpdater = $entityWithValueUpdater;
        $this->validator = $validator;
        $this->entityWithValueSaver = $entityWithValueSaver;
    }

    /**
     * @param string $entityIdentifier
     * @param string $attributeCode
     * @param array  $importedAssetCodes
     */
    public function add(
        string $entityIdentifier,
        string $attributeCode,
        array $importedAssetCodes
    ): void {
        $entityWithValues = $this->entityWithValueRepository->findOneByIdentifier($entityIdentifier);
        if (null === $entityWithValues) {
            throw new \InvalidArgumentException(sprintf(
                'Product or product model with identifier "%s" does not exist.',
                $entityIdentifier
            ));
        }

        $previousAssetCodes = [];
        $previousValue = $entityWithValues->getValue($attributeCode);
        if (null !== $previousValue) {
            $previousAssetCodes = array_map(function (string $assetCode) {
                return $assetCode;
            }, $previousValue->getData());
        }

        $this->entityWithValueUpdater->update($entityWithValues, [
            'values' => [
                $attributeCode => [[
                    'locale' => null,
                    'scope' => null,
                    'data' => array_merge($previousAssetCodes, $importedAssetCodes),
                ]],
            ],
        ]);

        $violations = $this->validator->validate($entityWithValues);
        if (0 < $violations->count()) {
            $violationMessages = '';
            foreach ($violations as $violation) {
                $violationMessages .= $violation->getMessage() . PHP_EOL;
            }

            throw new \InvalidArgumentException($violationMessages);
        }

        $this->entityWithValueSaver->save($entityWithValues);
    }
}
