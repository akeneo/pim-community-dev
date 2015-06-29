<?php

namespace Pim\Bundle\CatalogBundle\Updater;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Builder\FamilyBuilderInterface;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Updates a family.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyUpdater implements ObjectUpdaterInterface
{
    /** @var PropertyAccessor */
    protected $accessor;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $familyRepository;

    /** @var FamilyBuilderInterface */
    protected $familyBuilder;

    /**
     * @param IdentifiableObjectRepositoryInterface $familyRepository
     * @param FamilyBuilderInterface $familyBuilder
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $familyRepository,
        FamilyBuilderInterface $familyBuilder
    ) {
        $this->accessor         = PropertyAccess::createPropertyAccessor();
        $this->familyRepository = $familyRepository;
        $this->familyBuilder    = $familyBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function update($family, array $data, array $options = [])
    {
        if (!$family instanceof FamilyInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Pim\Bundle\CatalogBundle\Model\FamilyInterface", "%s" provided.',
                    ClassUtils::getClass($family)
                )
            );
        }

        foreach ($data as $field => $value) {
            $this->setData($family, $field, $value);
        }

        return $this;
    }

    /**
     * @param FamilyInterface $family
     * @param string          $field
     * @param mixed           $data
     */
    protected function setData(FamilyInterface $family, $field, $data)
    {
        if ('labels' === $field) {
            $this->familyBuilder->setLabels($family, $data);
        } elseif ('requirements' === $field) {
            $this->familyBuilder->setAttributeRequirements($family, $data);
        } elseif ('attributes' === $field) {
            $this->familyBuilder->addAttributes($family, $data);
        } else {
            $this->accessor->setValue($family, $field, $data);
        }
    }
}
