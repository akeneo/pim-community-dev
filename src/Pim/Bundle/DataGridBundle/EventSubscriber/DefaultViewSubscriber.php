<?php

namespace Pim\Bundle\DataGridBundle\EventSubscriber;

use Pim\Bundle\DataGridBundle\DataTransformer\DefaultViewDataTransformer;
use Pim\Bundle\DataGridBundle\Repository\DatagridViewRepositoryInterface;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Bundle\UserBundle\Event\UserFormBuilderEvent;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Default datagrid view subscriber for user preferences
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DefaultViewSubscriber implements EventSubscriberInterface
{
    /** @var DatagridViewRepositoryInterface */
    protected $datagridViewRepo;

    /**
     * @param DatagridViewRepositoryInterface $datagridViewRepo
     */
    public function __construct(DatagridViewRepositoryInterface $datagridViewRepo)
    {
        $this->datagridViewRepo = $datagridViewRepo;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            UserFormBuilderEvent::POST_BUILD => 'postBuild',
            FormEvents::PRE_SET_DATA         => 'preSetData'
        ];
    }

    /**
     * After build event listener
     *
     * @param UserFormBuilderEvent $event
     */
    public function postBuild(UserFormBuilderEvent $event)
    {
        $builder = $event->getSubject();
        $builder->addModelTransformer(new DefaultViewDataTransformer($this->datagridViewRepo));
        $builder->addEventSubscriber($this);
    }

    /**
     * On form pre set data
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $user = $event->getData();
        if (!$user instanceof UserInterface) {
            return;
        }

        $form = $event->getForm();

        $types = $this->datagridViewRepo->getDatagridViewTypeByUser($user);
        foreach ($types as $type) {
            $alias = $type['datagridAlias'];
            $form->add(
                'default_' . str_replace('-', '_', $alias) . '_view',
                EntityType::class,
                [
                    'class'         => 'PimDataGridBundle:DatagridView',
                    'choice_label'  => 'label',
                    'label'         => 'user.default_' . str_replace('-', '_', $alias) . '_view.label',
                    'query_builder' => function (DatagridViewRepositoryInterface $gridViewRepository) use ($alias) {
                        return $gridViewRepository->findDatagridViewByAlias($alias);
                    },
                    'required'      => false,
                    'attr'          => [
                        'data-type' => 'default-grid-view'
                    ]
                ]
            );
        }
    }
}
