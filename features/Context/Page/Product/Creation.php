<?php

namespace Context\Page\Product;

use Behat\Mink\Element\Element;
use Context\Page\Base\Form;
use Context\Spin\TimeoutException;

/**
 * Product creation page
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Creation extends Form
{
    /** @var string */
    protected $path = '#/enrich/product/create';

    /**
     * {@inheritdoc}
     *
     * @throws TimeoutException
     */
    public function fillField($locator, $value, Element $modal = null)
    {
        $selectContainer = $this->spin(function () use ($modal) {
            if (null === $modal) {
                return false;
            }

            return $modal->find('css', '.select2-container');
        }, 'Cannot find ".select2-container" in modal');

        $placeholder = $selectContainer->find('css', sprintf('.select2-chosen:contains("%s")', $locator));

        if ($placeholder) {
            $this->fillSelect2Field($selectContainer, $value);
        } else {
            parent::fillField($locator, $value, $modal);
        }
    }

    /**
     * Find a validation tooltip containing a text
     *
     * @param string $text
     *
     * @return null|Element
     */
    public function findValidationTooltip($text)
    {
        return $this->spin(function () use ($text) {
            return $this->find(
                'css',
                sprintf(
                    '.validation-errors .error-message:contains("%s")',
                    $text
                )
            );
        }, sprintf('Cannot find error message "%s" in validation tooltip', $text));
    }
}
