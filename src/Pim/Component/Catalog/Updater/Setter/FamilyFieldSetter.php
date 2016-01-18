<?php

namespace Pim\Component\Catalog\Updater\Setter;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

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

        if (null !== $data && '' !== $data) {
            $family = $this->getFamily($data);
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
        if (!is_string($data) && null !== $data && '' !== $data) {
            throw InvalidArgumentException::stringExpected(
                $field,
                'setter',
                'family',
                gettype($data)
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
