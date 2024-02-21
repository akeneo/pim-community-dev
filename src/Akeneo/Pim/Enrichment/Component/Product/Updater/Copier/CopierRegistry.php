<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Copier;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * Registry of copiers
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CopierRegistry implements CopierRegistryInterface
{
    /** @var AttributeCopierInterface[] priorized attribute copiers */
    protected $attributeCopiers = [];

    /** @var FieldCopierInterface[] priorized field copiers */
    protected $fieldCopiers = [];

    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     */
    public function __construct(IdentifiableObjectRepositoryInterface $repository)
    {
        $this->attributeRepository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function register(CopierInterface $copier)
    {
        if ($copier instanceof FieldCopierInterface) {
            $this->fieldCopiers[] = $copier;
        }
        if ($copier instanceof AttributeCopierInterface) {
            $this->attributeCopiers[] = $copier;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCopier($fromProperty, $toProperty)
    {
        $fromAttribute = $this->getAttribute($fromProperty);
        $toAttribute = $this->getAttribute($toProperty);
        if (null !== $fromAttribute && null !== $toAttribute) {
            $copier = $this->getAttributeCopier($fromAttribute, $toAttribute);
        } else {
            $copier = $this->getFieldCopier($fromProperty, $toProperty);
        }

        return $copier;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldCopier($fromField, $toField)
    {
        foreach ($this->fieldCopiers as $copier) {
            if ($copier->supportsFields($fromField, $toField)) {
                return $copier;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeCopier(AttributeInterface $fromAttribute, AttributeInterface $toAttribute)
    {
        foreach ($this->attributeCopiers as $copier) {
            if ($copier->supportsAttributes($fromAttribute, $toAttribute)) {
                return $copier;
            }
        }

        return null;
    }

    /**
     * @param string $code
     *
     * @return AttributeInterface|null
     */
    protected function getAttribute($code)
    {
        return $this->attributeRepository->findOneByIdentifier($code);
    }
}
