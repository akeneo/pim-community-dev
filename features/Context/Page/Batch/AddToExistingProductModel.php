<?php

namespace Context\Page\Batch;

use Behat\Mink\Element\Element;
use Context\Page\Base\Wizard;
use Context\Spin\TimeoutException;

/**
 * Add to existing product model step page
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddToExistingProductModel extends Wizard
{
    /**
     * {@inheritdoc}
     */
    public function fillField($locator, $value, Element $modal = null)
    {
        $selectContainers = $this->spin(function () {
            return $this->findAll('css', '.select2-container');
        }, 'Cannot find ".select2-container" in modal');

        $matchingContainer = null;

        foreach ($selectContainers as $container) {
            if ($container->find('css', sprintf('.select2-chosen:contains("%s")', $locator))) {
                $matchingContainer = $container;
            }
        }

        if ($matchingContainer) {
            $this->fillSelect2Field($matchingContainer, $value);
        } else {
            parent::fillField($locator, $value, $modal);
        }
    }
}
