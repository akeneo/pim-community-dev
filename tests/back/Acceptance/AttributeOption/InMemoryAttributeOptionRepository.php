<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\AttributeOption;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;

/**
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryAttributeOptionRepository implements AttributeOptionRepositoryInterface, SaverInterface
{
    /** @var AttributeOptionInterface[] */
    private $attributeOptions = [];

    /**
     * @param AttributeOptionInterface[] $attributeOptions
     */
    public function __construct(array $attributeOptions = [])
    {
        foreach ($attributeOptions as $attributeOption) {
            $this->save($attributeOption);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties(): array
    {
        return ['attribute', 'code'];
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier): ?AttributeOptionInterface
    {
        [$attributeCode, $attributeOptionCode] = \explode('.', $identifier);

        foreach ($this->attributeOptions as $attributeOption) {
            if (strtolower($attributeOption->getCode()) === strtolower($attributeOptionCode)
                && strtolower($attributeOption->getAttribute()->getCode()) === strtolower($attributeCode)) {
                return $attributeOption;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function save($attributeOption, array $options = []): void
    {
        if (false === $attributeOption instanceof AttributeOptionInterface) {
            throw new \InvalidArgumentException(sprintf(
                'The "object" argument should be an instance of "%s"',
                AttributeOptionInterface::class
            ));
        }

        if (null === $attributeOption->getAttribute()) {
            throw new \InvalidArgumentException(
                sprintf('AttributeOption "%s" should have an Attribute.', $attributeOption->getCode())
            );
        }

        $this->attributeOptions[] = $attributeOption;
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): array
    {
        if ($orderBy !== null || $limit !== null || $offset !== null) {
            throw new \InvalidArgumentException(
                sprintf('Argument "orderBy", "limit" and "offset" are not implemented yet')
            );
        }

        $result = [];
        foreach ($this->attributeOptions as $attributeOption) {
            $keepThisAttributeOption = true;

            foreach ($criteria as $searchedFieldName => $searchedValue) {
                $getter = \sprintf('get%s', ucfirst($searchedFieldName));

                if ($attributeOption->$getter() !== $searchedValue
                    || ($searchedValue instanceof AttributeInterface
                        && $attributeOption->$getter()->getCode() !== $searchedValue->getCode())) {
                    $keepThisAttributeOption = false;
                }
            }

            if (true === $keepThisAttributeOption) {
                $result[] = $attributeOption;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function findCodesByIdentifiers($attributeCode, array $attributeOptionCodes): array
    {
        $result = [];

        foreach ($this->attributeOptions as $attributeOption) {
            if ($attributeCode === $attributeOption->getAttribute()->getCode()
                && \in_array($attributeOption->getCode(), $attributeOptionCodes)) {
                $result[] = ['code' => $attributeOption->getCode()];
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName()
    {
        throw new NotImplementedException(__METHOD__);
    }
}
