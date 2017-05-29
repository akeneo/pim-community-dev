<?php

namespace Pim\Behat\Decorator\Completeness;

use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * This class contains methods to find specific completeness blocks from the panel in the product edit form.
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PanelDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /** @var array Selectors to ease find */
    protected $selectors = [
        'Completeness blocks' => ['css' => '.completeness-block']
    ];

    /**
     * Return Completeness Panel as an array
     * [
     *      'en_US' => [
     *          'opened'   => true,
     *          'position' => 1,
     *          'data'     => [
     *              'mobile' => [
     *                  'ratio'          => '90%',
     *                  'state'          => 'warning',
     *                  'missing_values' => [
     *                      'price' => 'Price'
     *                  ]
     *              ]
     *          ]
     *      ], ...
     * ]

     * @return array
     */
    public function getCompletenessData()
    {
        $completenesses = [];

        $completenessBlocks = $this->findAll('css', $this->selectors['Completeness blocks']['css']);
        foreach ($completenessBlocks as $position => $block) {
            $locale = $block->find('css', '.locale')->getAttribute('data-locale');
            $opened = 'false' === $block->getAttribute('data-closed');

            $completenesses[$locale] = [
                'opened'   => $opened,
                'position' => $position + 1,
                'data'     => [],
            ];

            $scopeBlocks = $block->findAll('css', '.content > div');
            foreach ($scopeBlocks as $scopeBlock) {
                $scope = $scopeBlock->find('css', '.channel')->getAttribute('data-channel');
                $completenesses[$locale]['data'][$scope] = $this->getScopeData($scopeBlock);
            }
        }

        return $completenesses;
    }

    /**
     * @param NodeElement $scopeBlock
     *
     * @return array
     */
    protected function getScopeData(NodeElement $scopeBlock)
    {
        $ratio = $scopeBlock ? $scopeBlock->find('css', '.literal-progress')->getHtml() : '';
        $state = $this->getState($scopeBlock);
        $label = $scopeBlock->find('css', '.channel')->getText();

        $missingValuesBlocks = $scopeBlock ? $scopeBlock->findAll('css', '.missing-attributes [data-attribute]') : [];

        $missingValues = [];
        if (!empty($missingValuesBlocks)) {
            foreach ($missingValuesBlocks as $missingValuesBlock) {
                $attributeCode  = $missingValuesBlock->getAttribute('data-attribute');
                $attributeLabel = $missingValuesBlock->getHtml();
                $missingValues[$attributeCode] = $attributeLabel;
            }
        }

        return [
            'label'          => $label,
            'ratio'          => $ratio,
            'state'          => $state,
            'missing_values' => $missingValues
        ];
    }

    /**
     * Get the state of a completeness from its progressbar classes.
     *
     * @param NodeElement $scopeBlock
     *
     * @return string|null
     */
    protected function getState(NodeElement $scopeBlock)
    {
        $stateClasses = [
            'AknProgress--warning' => 'warning',
            'AknProgress--apply'   => 'success',
        ];
        foreach ($stateClasses as $stateClass => $state) {
            if ($scopeBlock->find('css', '.progress')->hasClass($stateClass)) {
                return $state;
            }
        }

        return null;
    }
}
