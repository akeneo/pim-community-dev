<?php

namespace Context\Page\Export;

use Context\Page\Job\Show as JobShow;

/**
 * Export show page
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Show extends JobShow
{
    /**
     * @var string
     */
    protected $path = '#/spread/export/{id}';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            [
                'Export now button' => ['css' => '.navbar-buttons .export-btn'],
            ]
        );
    }

    /**
     * Click the job execution link
     */
    public function execute()
    {
        $this->getElement('Export now button')->click();
    }
}
