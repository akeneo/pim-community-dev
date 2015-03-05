<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Updater\Setter\FieldSetterInterface;

/**
 * Sets the family field
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyFieldSetter extends AbstractFieldSetter
{
    /**
     * @param IdentifiableObjectRepositoryInterface $familyRepository
     * @param array                                 $supportedFields
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $familyRepository,
        array $supportedFields
    ) {
        $this->familyRepository = $familyRepository;
        $this->supportedFields  = $supportedFields;
    }

    /**
     * {@inheritdoc}
     *
     * Expected data input format : "family_code"
     */
    public function setFieldData(ProductInterface $product, $field, $data, array $options = [])
    {
        $this->checkData($field, $data);

        if (null !== $data) {
            $family = $this->familyRepository->findOneByIdentifier($data);
            if (null === $family) {
                throw InvalidArgumentException::expected(
                    $field,
                    'existing family code',
                    'setter',
                    'family',
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
     */
    protected function checkData($field, $data)
    {
        if (!is_string($data) && null !== $data) {
            throw InvalidArgumentException::stringExpected(
                $field,
                'setter',
                'family',
                gettype($data)
            );
        }
    }
}
