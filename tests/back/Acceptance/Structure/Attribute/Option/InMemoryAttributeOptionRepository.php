<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Structure\Attribute\Option;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Repository\AttributeOptionRepositoryInterface;

/**
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryAttributeOptionRepository implements AttributeOptionRepositoryInterface, SaverInterface
{
    /** @var Collection */
    private $attributeOptions;

    /**
     * @param AttributeOptionInterface[] $attributeOptions
     */
    public function __construct(array $attributeOptions = [])
    {
        $this->attributeOptions = new ArrayCollection($attributeOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['code'];
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        return $this->attributeOptions->get($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function save($attributeOption, array $options = [])
    {
        if (!$attributeOption instanceof AttributeOptionInterface) {
            throw new \InvalidArgumentException('The object argument should be a attribute option');
        }

        $this->attributeOptions->set($attributeOption->getCode(), $attributeOption);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $attributeOptions = [];
        foreach ($this->attributeOptions as $attributeOption) {
            $keepThisAttributeOption = true;
            foreach ($criteria as $key => $value) {
                $getter = sprintf('get%s', ucfirst($key));
                if ($attributeOption->$getter() !== $value) {
                    $keepThisAttributeOption = false;
                }
            }

            if ($keepThisAttributeOption) {
                $attributeOptions[] = $attributeOption;
            }
        }

        return $attributeOptions;
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
    public function findCodesByIdentifiers($code, array $optionCodes)
    {
        throw new NotImplementedException(__METHOD__);
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
