<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Form\View\ViewUpdater;

use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Manager\RuleLinkedResourceManager;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Set rule impacted attributes as smart attribute
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class SmartViewUpdater implements ViewUpdaterInterface
{
    /**
     * @param RuleLinkedResourceManager $ruleLinkedResManager
     * @param UrlGeneratorInterface     $urlGenerator
     */
    public function __construct(
        RuleLinkedResourceManager $ruleLinkedResManager,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->ruleLinkedResManager = $ruleLinkedResManager;
        $this->urlGenerator         = $urlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function update(array $views, $key, $name)
    {
        $this->checkIfSmartAttribute($views, $key, $name);
    }

    /**
     * Mark an attribute as impacted by a rule
     *
     * @param FormView $view
     * @param int      $attributeId
     */
    protected function markAttributeAsSmart(FormView $view, $attributeId)
    {
        $value = $view->vars['value'];
        if (!$value instanceof ProductValueInterface) {
            return;
        }

        $url = $this->urlGenerator->generate(
            'pimee_catalog_rule_index',
            [
                'resourceId'   => $attributeId,
                'resourceType' => 'attribute',
            ]
        );

        foreach ($view as $child) {
            $child->vars['smart'] = $url;
        }
    }

    /**
     * Check if an attribute is smart or not
     *
     * @param array  $views
     * @param string $key
     * @param string $name
     */
    protected function checkIfSmartAttribute(array $views, $key, $name)
    {
        if ((isset($views[$key]['attributes'][$name]['value'])
            && $this->ruleLinkedResManager->isAttributeImpacted($views[$key]['attributes'][$name]))
        ) {
            $this->markAttributeAsSmart(
                $views[$key]['attributes'][$name]['value'],
                $views[$key]['attributes'][$name]['id']
            );
        } elseif (isset($views[$key]['attributes'][$name]['values'])) {
            foreach (array_keys($views[$key]['attributes'][$name]['values']) as $scope) {
                if ($this->ruleLinkedResManager->isAttributeImpacted($views[$key]['attributes'][$name])) {
                    $this->markAttributeAsSmart(
                        $views[$key]['attributes'][$name]['values'][$scope],
                        $views[$key]['attributes'][$name]['id']
                    );
                }
            }
        }
    }
}
