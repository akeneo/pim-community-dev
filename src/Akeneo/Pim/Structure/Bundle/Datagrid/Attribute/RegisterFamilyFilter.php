<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Datagrid\Attribute;

use Akeneo\Pim\Structure\Component\Query\InternalApi\GetAllFamiliesLabelByLocaleQueryInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\TranslatedLabelsProviderInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;

class RegisterFamilyFilter
{
    private GetAllFamiliesLabelByLocaleQueryInterface $familyRepository;

    private UserContext $userContext;

    public function __construct(GetAllFamiliesLabelByLocaleQueryInterface $familiesLabelByLocaleQuery, UserContext $userContext)
    {
        $this->familyRepository = $familiesLabelByLocaleQuery;
        $this->userContext = $userContext;
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
                        'choices' => array_flip($this->familyRepository->execute($this->userContext->getCurrentLocaleCode())),
                    ],
                ],
            ]
        ];

        $config = $event->getConfig();
        $config->offsetAddToArrayByPath(FilterConfiguration::COLUMNS_PATH, $filter);
    }
}
