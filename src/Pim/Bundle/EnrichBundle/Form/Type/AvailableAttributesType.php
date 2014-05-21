<?php

namespace Pim\Bundle\EnrichBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Symfony\Component\OptionsResolver\Options;
use Pim\Bundle\UserBundle\Context\UserContext;
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

    /** @var AttributeRepository */
    protected $repository;

    /** @var UserContext */
    protected $userContext;

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * Constructor
     *
     * @param string              $attributeClass
     * @param AttributeRepository $repository
     * @param UserContext         $userContext
     * @param TranslatorInterface $translator
     */
    public function __construct(
        $attributeClass,
        AttributeRepository $repository,
        UserContext $userContext,
        TranslatorInterface $translator
    ) {
        $this->attributeClass = $attributeClass;
        $this->repository     = $repository;
        $this->userContext    = $userContext;
        $this->translator     = $translator;
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
                'repository' => $this->repository,
                'repository_options' => [
                    'excluded_attribute_ids' => $options['attributes'],
                    'locale_code'            => $this->userContext->getCurrentLocaleCode(),
                    'default_group_label'    => $this->translator->trans(
                        'Other',
                        array(),
                        null,
                        $this->userContext->getCurrentLocaleCode()
                    ),
                ],
                'multiple' => true,
                'expanded' => false,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Pim\Bundle\CatalogBundle\Model\AvailableAttributes',
                'attributes' => array(),
            )
        );

        $resolver->setNormalizers(
            [
                'attributes' => function (Options $options, $value) {
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
                }
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_available_attributes';
    }
}
