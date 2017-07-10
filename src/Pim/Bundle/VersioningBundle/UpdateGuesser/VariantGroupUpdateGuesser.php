<?php

namespace Pim\Bundle\VersioningBundle\UpdateGuesser;

use Akeneo\Bundle\StorageUtilsBundle\Doctrine\SmartManagerRegistry;
use Doctrine\ORM\EntityManager;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;

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
    protected $repository;

    /**
     * @param GroupRepositoryInterface $repository
     */
    public function __construct(GroupRepositoryInterface $repository)
    {
        $this->repository = $repository;
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
            $variantGroup = $this->repository->getVariantGroupByProductTemplate($entity);

            if (null !== $variantGroup) {
                $pendings[] = $variantGroup;
            }
        }

        return $pendings;
    }
}
