<?php

namespace Context\Page;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

/**
 * Export creation page
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExportCreation extends Page
{
    protected $path = '/ie/export/create';


    protected $elements = array(
        'Channel selector' => array('css' => '#pim_import_export_job_jobDefinition_steps_0_reader_channel'),
        'With header'      => array('css' => '#pim_import_export_job_jobDefinition_steps_0_processor_withHeader'),
        'Tabs'             => array('css' => '#form-navbar'),
    );

    public function selectChannel($channel)
    {
        $this
            ->getElement('Channel selector')
            ->selectOption($channel);
    }

    public function checkField($field)
    {
        $this
            ->getElement($field)
            ->check();
    }

    public function save()
    {
        $this->pressButton('Save');
    }

    public function getUrl(array $options)
    {
        return sprintf('%s?%s', $this->getPath(), http_build_query($options));
    }

    public function visitTab($tab)
    {
        $this->getElement('Tabs')->clickLink($tab);
    }
}
