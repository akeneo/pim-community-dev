<?php

namespace Pim\Bundle\CatalogBundle\Updater\Copier;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;

/**
 * Registry of copiers
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CopierRegistry implements CopierRegistryInterface
{
    /** @var AttributeRepository */
    protected $attributeRepository;

    /** @var array */
    protected $copiers;

    /**
     * @param AttributeRepository $repository
     */
    public function __construct(AttributeRepository $repository)
    {
        $this->attributeRepository = $repository;
        $this->copiers = [];
    }

    /**
     * {@inheritdoc}
     */
    public function register(CopierInterface $copier)
    {
        $this->copiers[] = $copier;
    }

    /**
     * {@inheritdoc}
     */
    public function get($fromField, $toField)
    {
        $fromAttribute = $this->attributeRepository->findOneByCode($fromField);
        $toAttribute = $this->attributeRepository->findOneByCode($toField);

        // TODO : other possiblity is to use AttributeInterface in supports method

        $copier = null;
        if ($fromAttribute !== null && $toAttribute !== null) {
            foreach ($this->copiers as $currentCopier) {
                if ($currentCopier->supports($fromAttribute->getAttributeType(), $toAttribute->getAttributeType())) {
                    $copier = $currentCopier;
                    break;
                }
            }
        }

        if ($copier === null) {
            throw new \LogicException(
                sprintf(
                    'Source field "%s" and destination field "%s" are not supported by any copier',
                    $fromField,
                    $toField
                )
            );
        }

        return $copier;
    }
}
