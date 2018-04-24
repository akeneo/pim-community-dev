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
     *      'ecommerce' => [
     *          'label'    => 'Ecommerce'
     *          'position' => 1,
     *          'data'     => [
     *              'en_US' => [
     *                  'ratio' => '90%',
     *                  'state' => 'warning',
     *                  'label' => 'German (Germany)'
     *              ]
     *          ]
     *      ], ...
     * ]
     *
     * @return array
     */
    public function getCompletenessData()
    {
        $completenesses = [];

        $completenessBlocks = $this->findAll('css', $this->selectors['Completeness blocks']['css']);
        foreach ($completenessBlocks as $position => $block) {
            $channelCode = $block->find('css', '.channel')->getAttribute('data-channel');

            $completenesses[$channelCode] = [
                'position' => $position + 1,
                'data'     => [],
                'label'    => $block->find('css', '.channel')->getText(),
            ];

            $localeBlocks = $block->findAll('css', '.content > div');
            foreach ($localeBlocks as $localeBlock) {
                $locale = $localeBlock->find('css', '.locale')->getAttribute('data-locale');
                $completenesses[$channelCode]['data'][$locale] = $this->getLocaleData($localeBlock);
            }
        }

        return $completenesses;
    }

    /**
     * @param NodeElement $localeBlock
     *
     * @return array
     */
    protected function getLocaleData(NodeElement $localeBlock)
    {
        $ratio = $localeBlock ? $localeBlock->find('css', '.literal-progress')->getHtml() : '';
        $state = $this->getState($localeBlock);
        $label = $localeBlock->find('css', '.locale')->getText();
        $missing = $localeBlock->find('css', '.missing');

        return [
            'label'          => $label,
            'ratio'          => $ratio,
            'state'          => $state,
            'missing_values' => (string) ((null !== $missing) ? intval($missing->getText()) : 0)
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
