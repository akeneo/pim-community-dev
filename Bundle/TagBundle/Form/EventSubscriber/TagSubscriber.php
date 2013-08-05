<?php

namespace Oro\Bundle\TagBundle\Form\EventSubscriber;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\TagBundle\Entity\Taggable;
use Oro\Bundle\TagBundle\Entity\TagManager;
use Oro\Bundle\TagBundle\Form\Transformer\TagTransformer;

/**
 * Class TagSubscriber
 * @package Oro\Bundle\TagBundle\Form\EventSubscriber
 *
 * Loads tagging and assign to entity on pre set
 * Works in way similar to data transformer
 */
class TagSubscriber implements EventSubscriberInterface
{
    /**
     * @var TagManager
     */
    protected $manager;

    /**
     * @var TagTransformer
     */
    protected $transformer;

    public function __construct(TagManager $manager, TagTransformer $transformer)
    {
        $this->manager = $manager;
        $this->transformer = $transformer;
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
     * Loads tagging and transform it to view data
     *
     * @param FormEvent $event
     */
    public function preSet(FormEvent $event)
    {
        $entity = $event->getForm()->getParent()->getData();

        if (!$entity instanceof Taggable) {
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

        // pass entity to transformer
        $this->transformer->setEntity($entity);

        $event->setData(
            array(
                'autocomplete' => null,
                'all'          => json_encode($tags),
                'owner'        => json_encode($ownTags)
            )
        );
    }

    /**
     * Transform submitted data to model data
     *
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $values = $event->getData();
        $entities = array(
            'all'   => array(),
            'owner' => array()
        );

        foreach (array_keys($entities) as $type) {
            if (isset($values[$type]) && !empty($values[$type])) {
                try {
                    if (!is_array($values[$type])) {
                        $values[$type] = json_decode($values[$type]);
                    }
                    $names[$type] = array();
                    foreach ($values[$type] as $value) {
                        if (!empty($value->name)) {
                            // new tag
                            $names[$type][] = $value->name;
                        }
                    }

                    $entities[$type] = $this->manager->loadOrCreateTags($names[$type]);
                } catch (\Exception $e) {
                    $entities[$type] = array();
                }
            }
        }

        $event->setData($entities);
    }
}
