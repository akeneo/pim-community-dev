<?php

namespace Context\Page\Product;

use Context\Page\Base\Form;
use Behat\Mink\Exception\ElementNotFoundException;
use Pim\Bundle\ProductBundle\Entity\AttributeGroup;

/**
 * Product edit page
 *
 * @author    Gildas Quéméner <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Edit extends Form
{
    /**
     * @var string $path
     */
    protected $path = '/enrich/product/{id}/edit';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = array())
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            array(
                'Locales dropdown' => array('css' => '#locale-switcher'),
                'Locales selector' => array('css' => '#pim_product_locales'),
                'Enable switcher'  => array('css' => '#pim_product_enabled'),
                'Updates grid'     => array('css' => '#history table.grid'),
                'Image preview'    => array('css' => '#lbImage'),
                'Completeness'     => array('css' => 'div#completeness')
            )
        );
    }

    /**
     * @param string $locale
     * @param string $content
     *
     * @return NodeElement|null
     */
    public function findLocaleLink($locale, $content = null)
    {
        $link = $this->getElement('Locales dropdown')->findLink($locale);

        if ($content) {
            if (strpos($link->getText(), $content) === false) {
                return null;
            }
        }

        return $link;
    }

    /**
     * @param string $language
     */
    public function selectLanguage($language)
    {
        $this->getElement('Locales selector')->selectOption(ucfirst($language), true);
    }

    /**
     * @param string $locale
     */
    public function switchLocale($locale)
    {
        $this->getElement('Locales dropdown')->clickLink($locale);
    }

    /**
     * @param string $locale
     * @param string $label
     *
     * @return NodeElement
     */
    public function findLocale($locale, $label)
    {
        return $this->getElement('Locales dropdown')->find(
            'css',
            sprintf(
                'a:contains("%s"):contains("%s")',
                strtoupper($locale),
                $label
            )
        );
    }

    /**
     * @param string $group
     *
     * @return integer
     */
    public function getFieldsCountFor($group)
    {
        return count($this->getFieldsForGroup($group));
    }

    /**
     * @param string $group
     *
     * @return NodeElement
     */
    public function getFieldsForGroup($group)
    {
        $locator = sprintf('#tabs-%s label', $group instanceof AttributeGroup ? $group->getId() : 0);

        return $this->findAll('css', $locator);
    }

    /**
     * @param string $name
     *
     * @return NodeElement
     */
    public function findField($name)
    {
        $label = $this->find('css', sprintf('label:contains("%s")', $name));

        if (!$label) {
            throw new ElementNotFoundException($this->getSession(), 'form label ', 'value', $name);
        }

        $field = $label->getParent()->find('css', 'input');

        if (!$field) {
            throw new ElementNotFoundException($this->getSession(), 'form field ', 'id|name|label|value', $name);
        }

        return $field;
    }

    /**
     * @param string $field
     *
     * @return NodeElement
     */
    public function getRemoveLinkFor($field)
    {
        $controlGroupNode = $this->findField($field)->getParent()->getParent()->getParent()->getParent()->getParent();

        return $controlGroupNode->find('css', '.remove-attribute');
    }

    /**
     * Disable a product
     *
     * @return Edit
     */
    public function disableProduct()
    {
        $this->getElement('Enable switcher')->uncheck();

        return $this;
    }

    /**
     * Enable a product
     *
     * @return Edit
     */
    public function enableProduct()
    {
        $this->getElement('Enable switcher')->check();

        return $this;
    }

    /**
     * @return integer
     */
    public function countUpdates()
    {
        return count($this->getElement('Updates grid')->findAll('css', 'tbody tr'));
    }

    /**
     * @return NodeElement|void
     */
    public function getImagePreview()
    {
        $preview = $this->getElement('Image preview');

        if (!$preview || false === strpos($preview->getAttribute('style'), 'display: block')) {
            return;
        }

        return $preview;
    }

    /**
     * Get the completeness content
     *
     * @return \Behat\Mink\Element\NodeElement
     */
    public function findCompletenessContent()
    {
        $completenessContent = $this->getElement('Completeness')->find('css', 'table#progress-table');
        if (!$completenessContent) {
            throw new \InvalidArgumentException('Completeness content not found !!!');
        }

        return $completenessContent;
    }

    /**
     * Find completeness channel
     * @param string $name the channel name
     * @throws \InvalidArgumentException
     * @return \Behat\Mink\Element\NodeElement
     */
    public function findCompletenessScope($name)
    {
        $channel = $this->findCompletenessContent()
             ->find(sprintf('th .channel:contains("%s")', $name));

        if (!$channel) {
            throw new \InvalidArgumentException(sprintf('Completeness for channel %s not found', $name));
        }

        return $channel;
    }

    /**
     * Find completeness locale
     * @param string $code
     * @throws \InvalidArgumentException
     * @return boolean
     */
    public function findCompletenessLocale($code)
    {
        $flag   = $this->findCompletenessContent()
            ->find('css', sprintf('td img.flag'));
        $locale = $this->findCompletenessContent()
            ->find('css', sprintf('td code.flag-language:contains("%s")'));

        if ($flag && $locale) {
            return true;
        } else {
            throw new \InvalidArgumentException(sprintf('Completeness for locale %s not found', $code));
        }
    }

    /**
     * Find a completeness cell from column and row (channel and locale codes)
     * @param string $columnCode (channel code)
     * @param string $rowCode    (locale code)
     * @throws \InvalidArgumentException
     *
     * @return \Behat\Mink\Element\NodeElement
     */
    protected function findCompletenessCell($columnCode, $rowCode)
    {
        $completenessTable = $this->findCompletenessContent();

        $columnIdx = 0;
        foreach ($completenessTable->findAll('css', 'thead th') as $index => $header) {
            if ($header->getText() === $columnCode) {
                $columnIdx = $index;
                break;
            }
        }
        if ($columnIdx === 0) {
            throw new \InvalidArgumentException(sprintf('Column %s not found', $columnCode));
        }

        $cells = $completenessTable->findAll('css', sprintf('tbody tr:contains("%s") td', $rowCode));
        if (!$cells || count($cells) < $columnIdx) {
            throw new \InvalidArgumentException(sprintf('Row %s not found', $rowCode));
        }

        return $cells[$columnIdx];
    }

    /**
     * @param string  $channelCode
     * @param string  $localeCode
     * @param string  $barType
     * @param string  $info
     * @param integer $ratio
     * @throws \InvalidArgumentException
     * @return boolean
     */
    public function checkCompleteness($channelCode, $localeCode, $barType, $info, $ratio)
    {
        $completenessCell = $this
            ->findCompletenessCell($channelCode, $localeCode)
            ->find('css', 'div.progress-cell');

        // check progress bar type
        if (!$completenessCell->find('css', sprintf('div.bar-%s', $barType))) {
            throw new \InvalidArgumentException(
                sprintf('Progress bar is not %s for %s:%s', $barType, $channelCode, $localeCode)
            );
        }

        // check message displayed bottom to the progress bar
        if ($barType === 'disabled' || $info === 'Completed') {
            $infoPassed = $completenessCell->getText() === $info;
        } else {
            $infoPassed = $completenessCell->find('css', sprintf('span.progress-info:contains("%s")', $info));
        }
        if (!$infoPassed) {
            throw new \InvalidArgumentException(
                sprintf('Message %s not found for %s:%s', $info, $channelCode, $localeCode)
            );
        }

        // check progress bar width
        $title = $completenessCell
            ->find('css', 'div.progress')
            ->getAttribute('title');

        $pattern = sprintf('/^%s%% completed/', $ratio);
        if (!$title || preg_match($pattern, $title) !== 1) {
            throw new \InvalidArgumentException(
                sprintf('Ratio %s not found for %s:%s', $ratio, $channelCode, $localeCode)
            );
        }

        return true;
    }

    /**
     * Find legend div
     * @throws \InvalidArgumentException
     * @return \Behat\Mink\Element\NodeElement
     */
    public function findCompletenessLegend()
    {
        $legend = $this->getElement('Completeness')->find('css', 'div#legend');
        if (!$legend) {
            throw new \InvalidArgumentException('Legend content not found !!!');
        }

        return $legend;
    }
}
