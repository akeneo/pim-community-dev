<?php

namespace Oro\Bundle\TagBundle\Form\EventSubscriber;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\TagBundle\Entity\Taggable;
use Oro\Bundle\TagBundle\Entity\TagManager;

class TagSubscriber implements EventSubscriberInterface
{
    /**
     * @var TagManager
     */
    protected $manager;

    public function __construct(TagManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA  => 'preSet',
            FormEvents::PRE_SUBMIT    => 'preSubmit'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function preSet(FormEvent $event)
    {
        $entity = $event->getForm()->getParent()->getData();

        if (!$entity instanceof Taggable || $entity->getTaggableId() == null) {
            // do nothing if new entity or some error
            return;
        }

        $tags = $this->manager->getPreparedArray($entity);
        $ownTags = array_filter(
            $tags,
            function ($item) {
                return isset($item['owner']) && $item['owner'];
            }
        );

        $event->setData(
            array(
                'autocomplete' => null,
                'all'          => json_encode($tags),
                'owner'          => json_encode($ownTags)
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function preSubmit(FormEvent $event)
    {
        $values = $event->getData();
        $entities = array(
            'all'   => array(),
            'owner' => array()
        );

        if (!isset($values['all'], $values['owner'])) {
            return $values;
        }

        foreach (array_keys($entities) as $type) {
            if (!is_array($values[$type])) {
                $values[$type] = json_decode($values[$type]);
            }

            $newValues[$type] = array_filter(
                $values[$type],
                function ($item) {
                    return !intval($item->id) && !empty($item->name);
                }
            );

            $newValues[$type] =  array_map(
                function ($item) {
                    return $item->name;
                },
                $newValues[$type]
            );

            $values[$type] = array_map(
                function ($item) {
                    return $item->id;
                },
                $values[$type]
            );

            if ($values[$type]) {
                $entities[$type] = $this->manager->loadTags($values[$type]);
            }

            if ($newValues[$type]) {
                $entities[$type] = array_merge($entities[$type], $this->manager->loadOrCreateTags($newValues[$type]));
            }
        }

        $event->setData($entities);
    }
}
