<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Converter;

use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Oro\Bundle\SecurityBundle\SecurityFacade;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassOperationConverter implements ConverterInterface
{
    const EDIT_COMMON_JOB_CODE = 'edit_common_attributes';

    /** @var UserContext */
    protected $userContext;

    /** @var ConverterInterface */
    protected $productValueConverter;

    /** @var AttributeConverterInterface */
    protected $localizedConverter;

    /** @var CollectionFilterInterface */
    protected $productValuesFilter;

    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var array */
    protected $acls = [
        'family'        => 'pim_enrich_product_change_family',
        'categories'    => 'pim_enrich_product_categories_view',
        'enabled'       => 'pim_enrich_product_change_state',
        'groups'        => 'pim_enrich_product_add_to_groups',
    ];

    /**
     * @param UserContext                 $userContext
     * @param ConverterInterface          $productValueConverter
     * @param AttributeConverterInterface $localizedConverter
     * @param CollectionFilterInterface   $productValuesFilter
     * @param SecurityFacade              $securityFacade
     */
    public function __construct(
        UserContext $userContext,
        ConverterInterface $productValueConverter,
        AttributeConverterInterface $localizedConverter,
        CollectionFilterInterface $productValuesFilter,
        SecurityFacade $securityFacade
    ) {
        $this->userContext           = $userContext;
        $this->productValueConverter = $productValueConverter;
        $this->localizedConverter    = $localizedConverter;
        $this->productValuesFilter   = $productValuesFilter;
        $this->securityFacade        = $securityFacade;
    }

    /**
     * {@inheritdoc}
     */
    public function convert(array $operation)
    {
        if ($operation['jobInstanceCode'] === self::EDIT_COMMON_JOB_CODE) {
            $values = $operation['actions'][0]['normalized_values'];

            $values = $this->productValueConverter->convert(
                $values
            );

            $values = $this->localizedConverter->convertToDefaultFormats(
                $values,
                [
                    'locale' => $this->userContext->getUiLocale()->getCode()
                ]
            );

            $values = $this->productValuesFilter->filterCollection($values, 'pim.internal_api.product_values_data.edit');

            $operation['actions'][0]['normalized_values'] = $values;
        }

        $operation['actions'] = array_filter($operation['actions'], function ($action) {
            if (!isset($action['field'])) {
                return true;
            }

            switch ($action['field']) {
                case 'enabled':
                    return $this->checkAclForType('enabled');
                break;
                case 'family':
                    return $this->checkAclForType('family');
                break;
                case 'categories':
                    return $this->checkAclForType('categories');
                break;
                case 'groups':
                    return $this->checkAclForType('groups');
                break;
                default:
                    return true;
                break;
            }
        });

        return $operation;
    }

    /**
     * Return whether the current user has ACL to do the given modification $type on the product
     *
     * @param string $type
     *
     * @return bool
     */
    protected function checkAclForType(string $type): bool
    {
        $acl = $this->getAclForType($type);

        return null === $acl || $this->securityFacade->isGranted($acl);
    }

    /**
     * Return which ACL should be used to filter data of specified type.
     *
     * @param string $type
     *
     * @return string|null
     */
    protected function getAclForType(string $type): ?string
    {
        return isset($this->acls[$type]) ? $this->acls[$type] : null;
    }
}
