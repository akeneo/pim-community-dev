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

use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service\CreateAttributeInterface;
use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service\EnsureFranklinAttributeGroupExistsInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Query\SelectActiveLocaleCodesManagedByFranklinQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeLabel;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeType;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeGroup;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Saver\AttributeSaver;
use Akeneo\Pim\Structure\Component\Factory\AttributeFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Updater\AttributeUpdater;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class CreateAttribute implements CreateAttributeInterface
{
    private $factory;
    private $updater;
    private $saver;
    private $validator;
    private $findOrCreateFranklinAttributeGroup;
    private $selectActiveLocaleCodesManagedByFranklinQuery;

    public function __construct(
        AttributeFactory $factory,
        AttributeUpdater $updater,
        AttributeSaver $saver,
        ValidatorInterface $validator,
        EnsureFranklinAttributeGroupExistsInterface $findOrCreateFranklinAttributeGroup,
        SelectActiveLocaleCodesManagedByFranklinQueryInterface $selectActiveLocaleCodesManagedByFranklinQuery
    ) {
        $this->factory = $factory;
        $this->updater = $updater;
        $this->saver = $saver;
        $this->validator = $validator;
        $this->findOrCreateFranklinAttributeGroup = $findOrCreateFranklinAttributeGroup;
        $this->selectActiveLocaleCodesManagedByFranklinQuery = $selectActiveLocaleCodesManagedByFranklinQuery;
    }

    public function create(
        AttributeCode $attributeCode,
        AttributeLabel $attributeLabel,
        AttributeType $attributeType
    ): void {
        $this->findOrCreateFranklinAttributeGroup->ensureExistence();

        $data = [
            'code' => (string) $attributeCode,
            'group' => FranklinAttributeGroup::CODE,
            'labels' => $this->prepareLabels($attributeLabel),
            'localizable' => false,
            'scopable' => false,
        ];

        /** @var AttributeInterface $attribute */
        $attribute = $this->factory->createAttribute((string) $attributeType);
        $this->updater->update($attribute, $data);

        $violations = $this->validator->validate($attribute);
        if (0 !== $violations->count()) {
            throw new ViolationHttpException($violations);
        }

        $this->saver->save($attribute);
    }

    private function prepareLabels(AttributeLabel $attributeLabel): array
    {
        $localeCodes = $this->selectActiveLocaleCodesManagedByFranklinQuery->execute();

        //array_fill_keys()
        $labels = [];
        foreach ($localeCodes as $localeCode) {
            $labels[(string) $localeCode] = (string) $attributeLabel;
        }

        return $labels;
    }
}
