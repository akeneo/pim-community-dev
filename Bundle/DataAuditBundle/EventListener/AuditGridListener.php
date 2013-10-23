<?php

namespace Oro\Bundle\DataAuditBundle\EventListener;

use Doctrine\ORM\EntityManager;

use Symfony\Component\PropertyAccess\PropertyAccessor;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;

class AuditGridListener
{
    const PATH_CHOICES = '[filters][columns][objectClass][choices]';

    /** @var EntityManager */
    protected $em;

    /** @var null|array */
    protected $objectClassChoices = null;

    /**
     * @param EntityManager $em
     * @param PropertyAccessor $propAccessor
     */
    public function __construct(EntityManager $em, PropertyAccessor $propAccessor)
    {
        $this->em = $em;
        $this->propAccessor = $propAccessor;
    }

    /**
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        if (is_null($this->objectClassChoices)) {
            $this->objectClassChoices = $this->getObjectClassOptions();
        }

        $config = $event->getConfig();
        $objectClassChoices = $this->propAccessor->getValue(
            $config,
            self::PATH_CHOICES
        );
        $objectClassChoices = $objectClassChoices ?: array();

        $objectClassChoices = array_merge($objectClassChoices, $this->objectClassChoices);
        $this->propAccessor->setValue(
            $config,
            self::PATH_CHOICES,
            $objectClassChoices
        );

        $event->setConfig($config);
    }

    /**
     * Get distinct object classes
     *
     * @return array
     */
    protected function getObjectClassOptions()
    {
        $options = array();

        $result = $this->em->createQueryBuilder()
            ->add('select', 'a.objectClass')
            ->add('from', 'Oro\Bundle\DataAuditBundle\Entity\Audit a')
            ->distinct('a.objectClass')
            ->getQuery()
            ->getArrayResult();

        foreach ((array) $result as $value) {
            $options[$value['objectClass']] = current(
                array_reverse(
                    explode('\\', $value['objectClass'])
                )
            );
        }

        return $options;
    }
}
