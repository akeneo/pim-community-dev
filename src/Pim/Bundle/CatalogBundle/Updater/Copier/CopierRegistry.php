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
    public function get($sourceField, $destField)
    {
        $sourceAttribute = $this->attributeRepository->findOneByCode($sourceField);
        $destAttribute = $this->attributeRepository->findOneByCode($destField);

        // TODO : other possiblity is to use AttributeInterface in supports method
        // TODO : dont see the point to add a field copier

        $copier = null;
        if ($sourceAttribute !== null && $destAttribute !== null) {
            foreach ($this->copiers as $currentCopier) {
                if ($currentCopier->supports($sourceAttribute->getAttributeType(), $destAttribute->getAttributeType())) {
                    $copier = $currentCopier;
                    break;
                }
            }
        }

        if ($copier === null) {
            throw new \LogicException(
                sprintf(
                    'Source field "%s" and destination field "%s" are not supported by any copier',
                    $sourceField,
                    $destField
                )
            );
        }

        return $copier;
    }
}
