<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Structure\Service;

use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service\FindOrCreateFranklinAttributeGroupInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Query\SelectEnglishActiveLocaleCodesQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeGroup;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeGroupRepositoryInterface;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class FindOrCreateFranklinAttributeGroup implements FindOrCreateFranklinAttributeGroupInterface
{
    private $factory;
    private $saver;
    private $repository;
    private $validator;
    private $englishActiveLocaleCodeQuery;

    public function __construct(
        SimpleFactoryInterface $factory,
        SaverInterface $saver,
        AttributeGroupRepositoryInterface $repository,
        ValidatorInterface $validator,
        SelectEnglishActiveLocaleCodesQueryInterface $englishActiveLocaleCodeQuery
    ) {
        $this->factory = $factory;
        $this->saver = $saver;
        $this->repository = $repository;
        $this->validator = $validator;
        $this->englishActiveLocaleCodeQuery = $englishActiveLocaleCodeQuery;
    }

    public function findOrCreate(): void
    {
        $attributeGroup = $this->repository->findOneByIdentifier(FranklinAttributeGroup::CODE);
        if ($attributeGroup instanceof AttributeGroupInterface) {
            return;
        }

        $this->createAttributeGroup();
    }

    private function createAttributeGroup(): void
    {
        $attributeGroup = $this->factory->create();
        $attributeGroup->setCode((string) FranklinAttributeGroup::CODE);

        $localeCodes = $this->englishActiveLocaleCodeQuery->execute();
        foreach ($localeCodes as $localeCode) {
            $attributeGroup->setLocale((string) $localeCode);
            $attributeGroup->setLabel(FranklinAttributeGroup::LABEL);
        }

        $violations = $this->validator->validate($attributeGroup);
        if (0 !== $violations->count()) {
            throw new ViolationHttpException($violations);
        }

        $this->saver->save($attributeGroup);
    }
}
