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
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeLabel;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeGroup;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Model\Write\Attribute;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Saver\AttributeSaver;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Factory\AttributeFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Updater\AttributeUpdater;
use Psr\Log\LoggerInterface;
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
    private $ensureFranklinAttributeGroupExists;
    private $selectActiveLocaleCodesManagedByFranklinQuery;
    private $logger;

    public function __construct(
        AttributeFactory $factory,
        AttributeUpdater $updater,
        AttributeSaver $saver,
        ValidatorInterface $validator,
        EnsureFranklinAttributeGroupExistsInterface $ensureFranklinAttributeGroupExists,
        SelectActiveLocaleCodesManagedByFranklinQueryInterface $selectActiveLocaleCodesManagedByFranklinQuery,
        LoggerInterface $logger
    ) {
        $this->factory = $factory;
        $this->updater = $updater;
        $this->saver = $saver;
        $this->validator = $validator;
        $this->ensureFranklinAttributeGroupExists = $ensureFranklinAttributeGroupExists;
        $this->selectActiveLocaleCodesManagedByFranklinQuery = $selectActiveLocaleCodesManagedByFranklinQuery;
        $this->logger = $logger;
    }

    public function create(Attribute $attribute): void
    {
        $this->ensureFranklinAttributeGroupExists->ensureExistence();

        $pimAttribute = $this->createPimAttribute($attribute);

        $this->saver->save($pimAttribute);
    }

    public function bulkCreate(array $attributesToCreate): array
    {
        $this->ensureFranklinAttributeGroupExists->ensureExistence();

        $pimAttributes = [];
        $createdAttributes = [];

        /** @var Attribute $attributeToCreate */
        foreach ($attributesToCreate as $attributeToCreate) {
            try {
                $pimAttributes[] = $this->createPimAttribute($attributeToCreate);
                $createdAttributes[] = $attributeToCreate;
            } catch (\Exception $exception) {
                $this->logger->warning(
                    sprintf('Franklin-Insights: The attribute "%s" could not be created', (string) $attributeToCreate->getCode()),
                    ['message' => $exception->getMessage()]
                );
            }
        }

        $this->saver->saveAll($pimAttributes);

        return $createdAttributes;
    }

    private function createPimAttribute(Attribute $attribute): AttributeInterface
    {
        $data = [
            'code' => (string) $attribute->getCode(),
            'group' => FranklinAttributeGroup::CODE,
            'labels' => $this->prepareLabels($attribute->getLabel()),
            'localizable' => false,
            'scopable' => false,
        ];

        if (AttributeTypes::NUMBER === (string) $attribute->getType()) {
            $data['decimals_allowed'] = true;
            $data['negative_allowed'] = true;
        }

        /** @var AttributeInterface $pimAttribute */
        $pimAttribute = $this->factory->createAttribute((string) $attribute->getType());
        $this->updater->update($pimAttribute, $data);

        $violations = $this->validator->validate($pimAttribute);
        if (0 !== $violations->count()) {
            throw new \Exception($violations->get(0)->getMessage());
        }

        return $pimAttribute;
    }

    private function prepareLabels(AttributeLabel $attributeLabel): array
    {
        $localeCodes = $this->selectActiveLocaleCodesManagedByFranklinQuery->execute();

        return array_fill_keys($localeCodes, (string) $attributeLabel);
    }
}
