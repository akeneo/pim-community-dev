<?php
/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Event;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Resource\Model\RemoverInterface;
use Pim\Component\Resource\Model\SaverInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\RuleLinkedResourceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class RuleLinkedResourceManager
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class RuleLinkedResourceManager implements SaverInterface, RemoverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManager            $objectManager
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(ObjectManager $objectManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->objectManager   = $objectManager;
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
    public function remove($ruleLinkedResource, array $options = [])
    {
        if (!$ruleLinkedResource instanceof RuleLinkedResourceInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a use  PimEnterprise\Bundle\CatalogRuleBundle\Model\RuleLinkedResourceInterface,
                    "%s" provided',
                    ClassUtils::getClass($ruleLinkedResource)
                )
            );
        }

        $options = array_merge(['flush' => true], $options);
        $this->objectManager->remove($ruleLinkedResource);
        if (true === $options['flush']) {
            $this->objectManager->flush();
        }
    }
}
