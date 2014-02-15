<?php

namespace Context\Page\Element;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;

/**
 * Datagrid configuration popin
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfigurationPopin extends Element
{
    /** @var array */
    protected $selector = ['css' => '.modal'];

    /**
     * Hide a column by dragging to the bucket
     *
     * @param string $label
     */
    public function hideColumn($label)
    {
        $this->getColumn($label)->dragTo($this->getBucket());
        $this->apply();
    }

    /**
     * Move a column
     *
     * @param string $source
     * @param string $target
     */
    public function moveColumn($source, $target)
    {
        $this->getColumn($source)->dragTo($this->getColumn($target));
        $this->apply();
    }

    /**
     * Get the whole columns container or a specific item
     *
     * @param string $label
     *
     * @return Element
     *
     * @throw ElementNotFoundException
     */
    protected function getColumn($label = null)
    {
        if ($label) {
            $column = $this->find('css', sprintf('#columns li:contains("%s")', $label));
        } else {
            $column = $this->find('css', '#columns');
        }

        if (!$column) {
            throw new \Exception('Columns container is not available');
        }

        return $column;
    }

    /**
     * Get the whole bucket container or a specific item
     *
     * @param string $label
     *
     * @return Element
     *
     * @throw ElementNotFoundException
     */
    protected function getBucket($label = null)
    {
        if ($label) {
            $bucket = $this->find('css', sprintf('#bucket li:contains("%s")', $label));
        } else {
            $bucket = $this->find('css', '#bucket');
        }

        if (!$bucket) {
            throw new \Exception('Bucket container is not available');
        }

        return $bucket;
    }

    /**
     * Apply the configuration
     */
    protected function apply()
    {
        $this->find('css', '.btn.ok')->click();
    }
}
