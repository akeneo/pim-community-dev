<?php

namespace Oro\Bundle\NotificationBundle\Form\Type;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EmailNotificationType extends AbstractType
{
    /**
     * @var array
     */
    protected $entityNameChoices = array();

    /**
     * @var array
     */
    protected $entitiesData = array();

    /**
     * @var array
     */
    protected $templateNameChoices = array();

    /**
     * @param array $entitiesConfig
     */
    public function __construct($entitiesConfig = array())
    {
        $this->entityNameChoices = array_map(
            function ($value) {
                return isset($value['name'])? $value['name'] : '';
            },
            $entitiesConfig
        );

        $this->entitiesData = $entitiesConfig;
        array_walk(
            $this->entitiesData,
            function (&$value, $key) {
                $reflection = new \ReflectionClass($key);
                $interfaces = $reflection->getInterfaceNames();

                /**
                 * @TODO change interface name when entityConfigBundle will provide responsibility of owner interface
                 */
                $value = array_search('Oro\\Bundle\\TagBundle\\Entity\\ContainAuthorInterface', $interfaces) !== false;
            }
        );

        $this->templateNameChoices = array();
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'entityName',
            'choice',
            array(
                'choices'            => $this->entityNameChoices,
                'multiple'           => false,
                'translation_domain' => 'config',
                'empty_value'        => '',
                'empty_data'         => null,
                'required'           => true,
                'attr'               => array(
                    'data-entities' => json_encode($this->entitiesData)
                )
            )
        );

        $builder->add(
            'event',
            'entity',
            array(
                'class'         => 'OroNotificationBundle:Event',
                'property'      => 'name',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC');
                },
                'empty_value'   => '',
                'empty_data'    => null,
                'required'      => true
            )
        );

        $builder->add(
            'template',
            'choice',
            array(
                'choices'          => $this->templateNameChoices,
                'empty_value'      => '',
                'empty_data'       => null,
                'multiple'         => false,
                'required'         => true
            )
        );

        $builder->add(
            'recipientList',
            'oro_notification_recipient_list',
            array(
                'required' => true,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'           => 'Oro\Bundle\NotificationBundle\Entity\EmailNotification',
                'intention'            => 'emailnotification',
                'extra_fields_message' => 'This form should not contain extra fields: "{{ extra_fields }}"',
                'cascade_validation'   => true,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'emailnotification';
    }
}
