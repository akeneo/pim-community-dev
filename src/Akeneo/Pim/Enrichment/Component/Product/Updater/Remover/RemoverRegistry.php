<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Remover;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * Registry of removers
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoverRegistry implements RemoverRegistryInterface
{
    /** @var AttributeRemoverInterface[] priorized attribute removers */
    protected $attributeRemovers = [];

    /** @var FieldRemoverInterface[] priorized field removers */
    protected $fieldRemovers = [];

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
    public function register(RemoverInterface $remover)
    {
        if ($remover instanceof FieldRemoverInterface) {
            $this->fieldRemovers[] = $remover;
        }
        if ($remover instanceof AttributeRemoverInterface) {
            $this->attributeRemovers[] = $remover;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRemover($property)
    {
        $attribute = $this->getAttribute($property);
        if (null !== $attribute) {
            $remover = $this->getAttributeRemover($attribute);
        } else {
            $remover = $this->getFieldRemover($property);
        }

        return $remover;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldRemover($field)
    {
        foreach ($this->fieldRemovers as $remover) {
            if ($remover->supportsField($field)) {
                return $remover;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeRemover(AttributeInterface $attribute)
    {
        foreach ($this->attributeRemovers as $remover) {
            if ($remover->supportsAttribute($attribute)) {
                return $remover;
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
