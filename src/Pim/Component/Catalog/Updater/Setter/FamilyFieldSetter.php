<?php

namespace Pim\Component\Catalog\Updater\Setter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Sets the family field
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyFieldSetter extends AbstractFieldSetter
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $familyRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $familyRepository
     * @param array                                 $supportedFields
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $familyRepository,
        array $supportedFields
    ) {
        $this->familyRepository = $familyRepository;
        $this->supportedFields = $supportedFields;
    }

    /**
     * {@inheritdoc}
     *
     * Expected data input format : "family_code"
     */
    public function setFieldData(ProductInterface $product, $field, $data, array $options = [])
    {
        $this->checkData($field, $data);

        if (null !== $data && '' !== $data) {
            $family = $this->getFamily($data);
            if (null === $family) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    $field,
                    'family code',
                    'The family does not exist',
                    static::class,
                    $data
                );
            }
            $product->setFamily($family);
        } else {
            $product->setFamily(null);
        }
    }

    /**
     * Check if data are valid
     *
     * @param string $field
     * @param mixed  $data
     *
     * @throws InvalidPropertyTypeException
     */
    protected function checkData($field, $data)
    {
        if (!is_string($data) && null !== $data) {
            throw InvalidPropertyTypeException::stringExpected(
                $field,
                static::class,
                $data
            );
        }
    }

    /**
     * @param string $familyCode
     *
     * @return FamilyInterface
     */
    protected function getFamily($familyCode)
    {
        $family = $this->familyRepository->findOneByIdentifier($familyCode);

        return $family;
    }
}
