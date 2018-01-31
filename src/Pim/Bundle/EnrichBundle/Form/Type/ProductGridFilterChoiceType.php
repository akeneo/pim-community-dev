<?php

namespace Pim\Bundle\EnrichBundle\Form\Type;

use Oro\Bundle\DataGridBundle\Datagrid\Manager;
use Pim\Component\Enrich\Provider\TranslatedLabelsProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Choice Type which displays product grid filter grouped by attribute group
 * System filters : default filters configured in the grid
 * Attribute filters : attributes usable in the grid
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductGridFilterChoiceType extends AbstractType
{
    /** @var TranslatedLabelsProviderInterface */
    protected $attributeProvider;

    /** @var string */
    protected $datagridName;

    /** @var array  */
    protected $excludedFilters = [];

    /** @var Manager */
    protected $datagridManager;

    /**
     * @param TranslatedLabelsProviderInterface $attributeProvider
     * @param Manager                           $datagridManager
     * @param string                            $datagridName
     * @param array                             $excludedFilters
     */
    public function __construct(
        TranslatedLabelsProviderInterface $attributeProvider,
        Manager $datagridManager,
        $datagridName,
        array $excludedFilters
    ) {
        $this->attributeProvider = $attributeProvider;
        $this->datagridManager = $datagridManager;
        $this->datagridName = $datagridName;
        $this->excludedFilters = $excludedFilters;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => $this->getSystemFilters() + $this->getAttributesAsFilter(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'pim_enrich_product_grid_filter_choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * Return the attributes usable in the grid as filter
     *
     * @return array
     */
    protected function getAttributesAsFilter()
    {
        return $this->attributeProvider->findTranslatedLabels(['useable_as_grid_filter' => true]);
    }

    /**
     * Return the filter configured in the grid ($datagridName)
     *
     * @return array
     */
    protected function getSystemFilters()
    {
        $systemFilters = $this->datagridManager->getConfigurationForGrid($this->datagridName)
            ->offsetGetByPath('[filters][columns]');

        $formattedSystemFilters = [];
        foreach ($systemFilters as $code => $systemFilter) {
            if (!in_array($code, $this->excludedFilters)) {
                $formattedSystemFilters['System'][$systemFilter['label']] = $code;
            }
        }

        return $formattedSystemFilters;
    }
}
