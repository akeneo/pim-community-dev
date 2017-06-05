<?php

namespace Pim\Bundle\VersioningBundle\UpdateGuesser;

use Akeneo\Bundle\StorageUtilsBundle\Doctrine\SmartManagerRegistry;
use Doctrine\ORM\EntityManager;
use Pim\Component\Catalog\Model\ProductTemplateInterface;

/**
 * Variant group update guesser
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupUpdateGuesser implements UpdateGuesserInterface
{
    /** @var SmartManagerRegistry */
    protected $registry;

    /** @var string */
    protected $groupClass;

    /**
     * @param SmartManagerRegistry $registry
     * @param string               $groupClass
     */
    public function __construct(SmartManagerRegistry $registry, $groupClass)
    {
        $this->registry = $registry;
        $this->groupClass = $groupClass;
    }

    /**
     * {@inheritdoc}
     */
    public function supportAction($action)
    {
        return in_array(
            $action,
            [UpdateGuesserInterface::ACTION_UPDATE_ENTITY]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function guessUpdates(EntityManager $em, $entity, $action)
    {
        $pendings = [];

        if ($entity instanceof ProductTemplateInterface) {
            $repository = $this->registry->getRepository($this->groupClass);
            $variantGroup = $repository->getVariantGroupByProductTemplate($entity);

            if (null !== $variantGroup) {
                $pendings[] = $variantGroup;
            }
        }

        return $pendings;
    }
}
