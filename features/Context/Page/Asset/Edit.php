<?php

namespace Context\Page\Asset;

use Behat\Mink\Exception\ElementNotFoundException;
use Context\Page\Base\Form;

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
        $field = $this->find('css', 'label:contains("End of use at")');
        $this->fillDateField($field, $date);
    }

    /**
     * @param string $locale
     *
     * @throws \Exception
     */
    public function switchLocale($locale)
    {
        $elt = $this->getElement('Locales dropdown')->find('css', 'span.dropdown-toggle');
        if (!$elt) {
            throw new \Exception('Could not find locale switcher.');
        }
        $elt->click();

        $elt = $this->getElement('Locales dropdown')->find('css', sprintf('a[title="%s"]', $locale));
        if (!$elt) {
            throw new \Exception(sprintf('Could not find locale "%s" in switcher.', $locale));
        }
        $elt->click();
    }

    public function deleteReferenceFile()
    {
        $deleteButton = $this->find('css', 'div.reference button.delete');
        if (!$deleteButton) {
            throw new ElementNotFoundException($this->getSession(), 'delete reference button');
        }
        $deleteButton->click();

        return true;
    }

    public function generateVariationFile($channel)
    {
        $variationContainer = $this->findVariationContainer($channel);

        $generateButton = $variationContainer->find('css', '.asset-generator a');
        if (!$generateButton) {
            throw new ElementNotFoundException($this->getSession(), 'generate variation button');
        }
        $generateButton->click();

        return true;
    }

    public function deleteVariationFile($channel)
    {
        $variationContainer = $this->findVariationContainer($channel);

        $deleteButton = $variationContainer->find('css', 'div.variation button.delete');
        if (!$deleteButton) {
            throw new ElementNotFoundException($this->getSession(), 'delete variation button');
        }
        $deleteButton->click();

        return true;
    }

    public function resetVariationsFiles()
    {
        $resetButton = $this->find('css', 'div.reference button.reset-variations');
        if (!$resetButton) {
            throw new ElementNotFoundException($this->getSession(), 'reset button');
        }
        $resetButton->click();

        return true;
    }

    public function findReferenceUploadZone()
    {
        $uploadZone = $this->find('css', 'div.reference .asset-uploader');

        if (!$uploadZone) {
            throw new ElementNotFoundException($this->getSession(), 'reference upload zone');
        }

        return $uploadZone;
    }

    public function findVariationUploadZone($channel)
    {
        $variationContainer = $this->findVariationContainer($channel);
        $uploadZone = $variationContainer->find('css', 'div.variation .asset-uploader');

        if (!$uploadZone) {
            throw new ElementNotFoundException($this->getSession(), 'variation upload zone');
        }

        return $uploadZone;
    }

    public function findVariationGenerateZone($channel)
    {
        $variationContainer = $this->findVariationContainer($channel);

        $generateZone = $variationContainer->find('css', 'span:contains("Generate from reference")');

        if (!$generateZone) {
            throw new ElementNotFoundException($this->getSession(), sprintf('variation %s generate zone', $channel));
        }

        return true;
    }

    public function findVariationContainer($channel)
    {
        $allVariationsContainer = $this->findAll('css', 'div.variation');

        if (!$allVariationsContainer) {
            throw new ElementNotFoundException($this->getSession(), 'variation containers');
        }

        foreach ($allVariationsContainer as $container) {
            $title = $this->find('css', sprintf('h4:contains("%s")', $channel));
            if (null !== $title) {
                return $container;
            }
        }

        throw new ElementNotFoundException($this->getSession(), sprintf('variation %s container', $channel));
    }
}
