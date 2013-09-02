<?php

namespace Oro\Bundle\EntityBundle\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Property\TwigTemplateProperty;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;

class CustomEntityDatagrid extends DatagridManager
{
    protected $configManager;

    protected $customEntity;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $this->getDynamicFields($fieldsCollection);
    }

    /**
     * @param FieldDescriptionCollection $fieldsCollection
     */
    protected function getDynamicFields(FieldDescriptionCollection $fieldsCollection)
    {
        $fields = array();
        foreach ($this->configManager->getProviders() as $provider) {
            foreach ($provider->getPropertyConfig()->getItems() as $code => $item) {
                if (isset($item['grid'])) {
                    $item['grid']        = $provider->getPropertyConfig()->initConfig($item['grid']);

                    $fieldObjectProvider = new FieldDescription();
                    $fieldObjectProvider->setName($code);
                    $fieldObjectProvider->setOptions(
                        array_merge(
                            $item['grid'],
                            array(
                                'expression' => 'cev' . $code . '.value',
                                'field_name' => $code,
                            )
                        )
                    );

                    if (isset($item['grid']['type'])
                        && $item['grid']['type'] == FieldDescriptionInterface::TYPE_HTML
                        && isset($item['grid']['template'])
                    ) {
                        $templateDataProperty = new TwigTemplateProperty(
                            $fieldObjectProvider,
                            $item['grid']['template']
                        );
                        $fieldObjectProvider->setProperty($templateDataProperty);
                    }

                    if (isset($item['options']['priority']) && !isset($fields[$item['options']['priority']])) {
                        $fields[$item['options']['priority']] = $fieldObjectProvider;
                    } else {
                        $fields[] = $fieldObjectProvider;
                    }
                }
            }
        }

        ksort($fields);
        foreach ($fields as $field) {
            $fieldsCollection->add($field);
        }
    }

    /**
     * @param ProxyQueryInterface $query
     * @return ProxyQueryInterface
     */
    protected function prepareQuery(ProxyQueryInterface $query)
    {
        foreach ($this->configManager->getProviders() as $provider) {
            foreach ($provider->getPropertyConfig()->getItems() as $code => $item) {
                $alias = 'cev' . $code;

                if (isset($item['grid']['query'])) {
                    $query->andWhere($alias . '.value ' . $item['grid']['query']['operator'] . ' :' . $alias);
                    $query->setParameter($alias, $item['grid']['query']['value']);
                }

                $query->leftJoin(
                    'ce.values',
                    $alias,
                    'WITH',
                    $alias . ".code='" . $code . "' AND " . $alias . ".scope='" . $provider->getScope() . "'"
                );
                $query->addSelect($alias . '.value as ' . $code, true);
            }
        }

        return $query;
    }
}
