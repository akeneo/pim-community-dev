<?php

require_once __DIR__ .'/OroRequirements.php';

/**
 * Akeneo PIM requirements
 *
 * This class specifies all requirements and optional recommendations that are necessary
 * to install and run Akeneo PIM application
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimRequirements extends OroRequirements
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Adds an Akeneo PIM specific requirement
     *
     * @param boolean     $fulFilled   Whether the requirement is fulfilled
     * @param string      $testMessage The message for testing the requirement
     * @param string      $helpHtml    The help text formatted in HTML for resolving the problem
     * @param string|null $helpText    The help text
     *     (when null, it will be inferred from $helpHtml, i.e. stripped from HTML tags)
     */
    public function addPimRequirement($fulFilled, $testMessage, $helpHtml, $helpText = null)
    {
        $this->add(new PimRequirement($fulfilled, $testMessage, $helpHtml, $helpText, false));
    }

    /**
     * Get the list of Akeneo PIM specific requirements
     *
     * @return array
     */
    public function getPimRequirements()
    {
        return array_filter($this->getRequirements(), function ($requirement) {
            return $requirement instanceof PimRequirement;
        });
    }
}

class PimRequirement extends Requirement
{
}
