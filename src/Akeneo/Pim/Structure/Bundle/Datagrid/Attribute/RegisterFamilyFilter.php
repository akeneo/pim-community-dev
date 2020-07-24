<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Datagrid\Attribute;

use Akeneo\Platform\Bundle\UIBundle\Provider\TranslatedLabelsProviderInterface;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;

class RegisterFamilyFilter
{
    /** @var TranslatedLabelsProviderInterface */
    private $familyRepository;

    public function __construct(TranslatedLabelsProviderInterface $familyRepository)
    {
        $this->familyRepository = $familyRepository;
    }

    public function buildBefore(BuildBefore $event): void
    {
        $filter = [
            'family' => [
                'type' => 'datagrid_attribute_family_filter',
                'ftype' => 'choice',
                'label' => 'Family',
                'data_name' => 'families',
                'options' => [
                    'field_options' => [
                        'multiple' => true,
                        'choices' => $this->familyRepository->findTranslatedLabels(),
                    ],
                ],
            ]
        ];

        $config = $event->getConfig();
        $config->offsetAddToArrayByPath(FilterConfiguration::COLUMNS_PATH, $filter);
    }
}
