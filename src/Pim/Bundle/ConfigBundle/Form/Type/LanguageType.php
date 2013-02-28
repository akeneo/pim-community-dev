<?php
namespace Pim\Bundle\ConfigBundle\Form\Type;

use Symfony\Component\Locale\Locale;

use Doctrine\ORM\EntityRepository;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\AbstractType;

/**
 * Type for language form
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LanguageType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('id', 'hidden');

        // Add locale field and fallback field
        $this->addLocaleField($builder);

        $this->addFallbackField($builder);

        // Add currency field
        $this->addCurrencyField($builder);

        $builder->add('activated', 'checkbox', array('required' => false));
    }

    /**
     * Add locale field
     * @param FormBuilderInterface $builder
     */
    protected function addLocaleField(FormBuilderInterface $builder)
    {
        $builder->add(
            'code',
            'choice',
            array(
                'choices' => Locale::getDisplayLocales(\Locale::getDefault()),
                'required' => true,
                'preferred_choices' => array('en', 'fr', 'en_US')
            )
        );
    }

    /**
     * Add fallback field
     * @param FormBuilderInterface $builder
     */
    protected function addFallbackField(FormBuilderInterface $builder)
    {
        $builder->add(
            'fallback',
            'choice',
            array(
                'choices' => Locale::getDisplayLocales(\Locale::getDefault()),
                'required' => true,
                'preferred_choices' => array('en', 'fr', 'en_US')
            )
        );
    }

    /**
     * Add currency field
     * @param FormBuilderInterface $builder
     */
    protected function addCurrencyField(FormBuilderInterface $builder)
    {
        $builder->add(
            'currencies',
            'entity',
            array(
                'class' => 'PimConfigBundle:Currency',
                'property' => 'code',
                'multiple' => true,
                'query_builder' => function (EntityRepository $repository) {
                    // prepare query to get activated currencies ordered by code
                    $query = $repository->createQueryBuilder('c');
                    $query->andwhere(
                        $query->expr()->eq('c.activated', true)
                    )
                    ->orderBy('c.code');

                    return $query;
                },
                'required' => true
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
                'data_class' => 'Pim\Bundle\ConfigBundle\Entity\Language'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_config_language';
    }
}
