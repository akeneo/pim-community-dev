<?php

namespace Pim\Bundle\EnrichBundle\Form\Type;

use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Enrich\Repository\TranslatedLabelsProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Type for available attributes
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AvailableAttributesType extends AbstractType
{
    /** @var string */
    protected $attributeClass;

    /** @var TranslatedLabelsProviderInterface */
    protected $attributeRepository;

    /** @var UserContext */
    protected $userContext;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var string */
    protected $dataClass;

    /**
     * Constructor
     *
     * @param TranslatedLabelsProviderInterface $attributeRepository
     * @param TranslatorInterface      $translator
     * @param string                   $attributeClass
     * @param string                   $dataClass
     */
    public function __construct(
        TranslatedLabelsProviderInterface $attributeRepository,
        TranslatorInterface $translator,
        $attributeClass,
        $dataClass
    ) {
        $this->attributeClass      = $attributeClass;
        $this->attributeRepository = $attributeRepository;
        $this->translator          = $translator;
        $this->dataClass           = $dataClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'attributes',
            'light_entity',
            [
                'repository'         => $this->attributeRepository,
                'repository_options' => [
                    'excluded_attribute_ids' => $options['excluded_attributes'],
                ],
                'multiple'           => true,
                'expanded'           => false,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'          => $this->dataClass,
                'excluded_attributes' => [],
            ]
        );

        $resolver->setNormalizer('excluded_attributes', function (Options $options, $value) {
            foreach ($value as $key => $attribute) {
                if (!$attribute instanceof $this->attributeClass) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Option "attributes" must only contains instances of "%s", got "%s"',
                            $this->attributeClass,
                            get_class($attribute)
                        )
                    );
                }
                $value[$key] = $attribute->getId();
            }

            return $value;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_available_attributes';
    }
}
