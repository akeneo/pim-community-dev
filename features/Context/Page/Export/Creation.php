<?php

namespace Context\Page\Export;

use Context\Page\Base\Form;

/**
 * Export creation page
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Creation extends Form
{
    protected $path = '/ie/export/create';

    public function __construct($session, $pageFactory, $parameters = array())
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            array(
                'Channel selector' => array('css' => '#pim_import_export_job_jobDefinition_steps_0_reader_channel'),
                'With header'      => array('css' => '#pim_import_export_job_jobDefinition_steps_0_processor_withHeader'),
            )
        );
    }

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

    public function getUrl(array $options)
    {
        return sprintf('%s?%s', $this->getPath(), http_build_query($options));
    }
}
