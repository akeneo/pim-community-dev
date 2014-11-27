<?php
/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Manager;

use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Resource\Model\RemoverInterface;
use Pim\Component\Resource\Model\SaverInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\RuleLinkedResourceInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class RuleLinkedResourceManager
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class RuleLinkedResourceManager implements SaverInterface, RemoverInterface
{
    /** @var ManagerRegistry */
    protected $managerRegistry;

    /**
     * Constructor
     *
     * @param ManagerRegistry          $managerRegistry
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(ManagerRegistry $managerRegistry, EventDispatcherInterface $eventDispatcher)
    {
        $this->managerRegistry = $managerRegistry;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function save($object, array $options = [])
    {
    }

    /**
     * {@inheritdoc}
     */
    public function remove($object, array $options = [])
    {
        if (!$object instanceof RuleLinkedResourceInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a use PimEnterprise\Bundle\CatalogRuleBundle\Model\RuleLinkedResourceInterface,
                    "%s" provided',
                    ClassUtils::getClass($object)
                )
            );
        }

        $options = array_merge(['flush' => true], $options);
        $em = $this->managerRegistry->getManagerForClass(get_class($object));
        $em->remove($object);

        if (true === $options['flush']) {
            $em->flush();
        }
    }
}
