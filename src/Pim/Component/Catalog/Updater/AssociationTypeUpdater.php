<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Component\StorageUtils\Updater\UnknownPropertyException;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Updates an association type.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTypeUpdater implements ObjectUpdaterInterface
{
    /** @var PropertyAccessor */
    protected $accessor;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $assocTypeRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $assocTypeRepository
     */
    public function __construct(IdentifiableObjectRepositoryInterface $assocTypeRepository)
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->associationTypeRepository = $assocTypeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function update($associationType, array $data, array $options = [])
    {
        if (!$associationType instanceof AssociationTypeInterface) {
            throw new InvalidObjectException(
                sprintf(
                    'Expects a "Pim\Component\Catalog\Model\AssociationTypeInterface", "%s" provided.',
                    ClassUtils::getClass($associationType)
                )
            );
        }

        foreach ($data as $field => $value) {
            $this->setData($associationType, $field, $value);
        }

        return $this;
    }

    /**
     * @param AssociationTypeInterface $associationType
     * @param string                   $field
     * @param mixed                    $data
     */
    protected function setData(AssociationTypeInterface $associationType, $field, $data)
    {
        if ('labels' === $field) {
            foreach ($data as $localeCode => $label) {
                $associationType->setLocale($localeCode);
                $translation = $associationType->getTranslation();
                $translation->setLabel($label);
            }
        } else {
            try {
                $this->accessor->setValue($associationType, $field, $data);
            } catch (\Exception $e) {
                throw new UnknownPropertyException($field, "A message");
            }
        }
    }
}
