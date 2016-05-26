<?php

namespace Pim\Bundle\ImportExportBundle\Form\Type\ProductExport;

use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Locale selector in the product export builder form
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleChoiceType extends AbstractType
{
    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /**
     * @param LocaleRepositoryInterface $localeRepository
     */
    public function __construct(LocaleRepositoryInterface $localeRepository)
    {
        $this->localeRepository = $localeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $locales = $this->localeRepository->getActivatedLocaleCodes();

        $resolver->setDefaults([
            'choices'  => array_combine($locales, $locales),
            'required' => true,
            'select2'  => true,
            'multiple' => true,
            'label'    => 'pim_connector.export.locales.label',
            'help'     => 'pim_connector.export.locales.help',
            'attr'     => ['data-tab' => 'content']
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_import_export_product_export_locale_choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }
}
