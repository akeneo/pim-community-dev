<?php

namespace Context\Page\Job;

use Context\Page\Base\Form;

/**
 * Job creation page
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Creation extends Form
{
    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = array())
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            array(
                'Channel selector' => array('css' => '#pim_import_export_jobInstance_job_steps_0_reader_channel'),
                'With header'      => array(
                    'css' => '#pim_import_export_jobInstance_job_steps_0_processor_withHeader'
                ),
            )
        );
    }

    /**
     * @param string $channel
     */
    public function selectChannel($channel)
    {
        $this->getElement('Channel selector')->selectOption($channel);
    }

    /**
     * @param string $field
     */
    public function checkField($field)
    {
        $this->getElement($field)->check();
    }
}
