<?php

namespace Context\Page\Asset;

use Behat\Mink\Exception\ElementNotFoundException;
use Context\Page\Base\Form;
use SensioLabs\Behat\PageObjectExtension\PageObject\Element;

/**
 * Product asset edit page
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Edit extends Form
{
    /** @var string */
    protected $path = '/enrich/asset/{id}/edit';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            [
                'Locales dropdown' => ['css' => '#locale-switcher'],
                'Category pane'    => ['css' => '#pimee_product_asset-tabs-categories'],
                'Category tree'    => [
                    'css'        => '#trees',
                    'decorators' => [
                        'Pim\Behat\Decorator\TreeDecorator\JsTreeDecorator'
                    ]
                ],
                'Main context selector' => [
                    'css'        => '.asset-variations-pane h3',
                    'decorators' => [
                        'Pim\Behat\Decorator\ContextSwitcherDecorator'
                    ]
                ]
            ]
        );
    }

    /**
     * Fill a new date in the End of use at date picker
     *
     * @param string $date YEAR-MONTH-DAY e.g. 2015-06-20
     */
    public function changeTheEndOfUseAtTo($date)
    {
        $field = $this->spin(function () {
            return $this->find('css', 'label:contains("End of use at")');
        }, '"End of use" field not found.');
        $this->fillDateField($field, $date);
    }

    /**
     * @throws ElementNotFoundException
     *
     * @return bool
     */
    public function deleteReferenceFile()
    {
        $deleteButton = $this->spin(function () {
            return $this->find('css', 'div.reference button.delete');
        }, 'Delete reference button not found.');

        $deleteButton->click();

        return true;
    }

    /**
     * @param string $channel
     *
     * @throws ElementNotFoundException
     *
     * @return bool
     */
    public function generateVariationFile($channel)
    {
        $variationContainer = $this->findVariationContainer($channel);

        $generateButton = $this->spin(function () use ($variationContainer) {
            return $variationContainer->find('css', '.asset-generator a');
        }, 'Generate variation button not found.');

        $generateButton->click();

        return true;
    }

    /**
     * @param string $channel
     *
     * @throws ElementNotFoundException
     *
     * @return bool
     */
    public function deleteVariationFile($channel)
    {
        $variationContainer = $this->findVariationContainer($channel);
        $deleteButton = $this->spin(function () use ($variationContainer) {
            return $variationContainer->find('css', 'div.variation button.delete');
        }, 'Delete variation button not found.');

        $deleteButton->click();

        return true;
    }

    /**
     * @throws ElementNotFoundException
     *
     * @return bool
     */
    public function resetVariationsFiles()
    {
        $resetButton = $this->spin(function () {
            return $this->find('css', 'div.reference button.reset-variations');
        }, 'Reset button not found.');

        $resetButton->click();

        return true;
    }

    /**
     * @throws ElementNotFoundException
     *
     * @return Element
     */
    public function findReferenceUploadZone()
    {
        return $this->spin(function () {
            return $this->find('css', 'div.reference .asset-uploader');
        }, 'Cannot find the reference upload zone');
    }

    /**
     * @param string $channel
     *
     * @throws ElementNotFoundException
     *
     * @return Element
     */
    public function findVariationUploadZone($channel)
    {
        $variationContainer = $this->findVariationContainer($channel);

        return $this->spin(function () use ($variationContainer) {
            return $variationContainer->find('css', 'div.variation .asset-uploader');
        }, 'Cannot find the variation upload zone');
    }

    /**
     * @param string $channel
     *
     * @throws ElementNotFoundException
     *
     * @return bool
     */
    public function findVariationGenerateZone($channel)
    {
        $variationContainer = $this->findVariationContainer($channel);

        $generateZone = $variationContainer->find('css', 'span:contains("Generate from reference")');

        if (!$generateZone) {
            throw new ElementNotFoundException($this->getSession(), sprintf('variation %s generate zone', $channel));
        }

        return true;
    }

    /**
     * @param string $channel
     *
     * @throws ElementNotFoundException
     *
     * @return Element
     */
    public function findVariationContainer($channel)
    {
        $allVariationsContainer = $this->spin(function () {
            return $this->findAll('css', 'div.variation');
        }, 'Cannot find the variation containers');

        foreach ($allVariationsContainer as $container) {
            $title = $this->find('css', sprintf('h4:contains("%s")', $channel));
            if (null !== $title) {
                return $container;
            }
        }

        throw new ElementNotFoundException($this->getSession(), sprintf('variation %s container', $channel));
    }

    /**
     * @param string $category
     *
     * @return Edit
     */
    public function selectTree($category)
    {
        $link = $this->getElement('Category pane')->find('css', sprintf('#trees-list li a:contains("%s")', $category));
        if (null === $link) {
            throw new \InvalidArgumentException(sprintf('Tree "%s" not found', $category));
        }
        $link->click();

        return $this;
    }
}
