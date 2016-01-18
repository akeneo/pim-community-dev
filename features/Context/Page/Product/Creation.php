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
    protected $path = '/enrich/product/create';

    /**
     * {@inheritdoc}
     *
     * @throws TimeoutException
     */
    public function fillField($locator, $value, Element $modal = null)
    {
        $selectContainer = $this->spin(function () use ($modal) {
            return $modal->find('css', '.select2-container');
        });

        $placeholder = $selectContainer->find('css', sprintf('.select2-chosen:contains("%s")', $locator));

        if ($placeholder) {
            $this->fillSelect2Field($selectContainer, $value);
        } else {
            parent::fillField($locator, $value, $modal);
        }
    }
}
