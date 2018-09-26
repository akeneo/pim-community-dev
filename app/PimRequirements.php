<?php

require_once __DIR__ . '/../var/SymfonyRequirements.php';

use Akeneo\Platform\CommunityRequirements;
use Akeneo\Platform\Requirement as PlatformRequirement;

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
class PimRequirements extends SymfonyRequirements
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $directoriesToCheck = [])
    {
        parent::__construct();

        $communityRequirements = new CommunityRequirements(__DIR__.'/..', $directoriesToCheck);

        foreach($communityRequirements->getRequirements() as $requirement) {
            if ($requirement->isMandatory()) {
                $this->addPimRequirement($requirement);
            } else {
                $this->addRecommendation(
                    $requirement->isFullfilled(),
                    $requirement->getTestMessage(),
                    $requirement->getHelpText()
                );
            }
        }
    }

    /**
     * Adds an Akeneo PIM specific mandatory requirement
     */
    private function addPimRequirement(PlatformRequirement $requirement)
    {
        $this->add(
            new PimRequirement(
                $requirement->isFullfilled(),
                $requirement->getTestMessage(),
                $requirement->getHelpText()
            )
        );
    }

    /**
     * Get the list of Akeneo PIM specific requirements
     */
    public function getPimRequirements(): array
    {
        return array_filter($this->getRequirements(), function ($requirement) {
            return $requirement instanceof PimRequirement;
        });
    }

    /**
     * Get the list of mandatory requirements (all requirements excluding PhpIniRequirement)
     */
    public function getMandatoryRequirements(): array
    {
        return array_filter($this->getRequirements(), function ($requirement) {
            return !($requirement instanceof PhpIniRequirement) && !($requirement instanceof PimRequirement);
        });
    }

    /**
     * Get the list of PHP ini requirements
     */
    public function getPhpIniRequirements(): array
    {
        return array_filter($this->getRequirements(), function ($requirement) {
            return $requirement instanceof PhpIniRequirement;
        });
    }
}

/**
 * PimRequirement class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimRequirement extends Requirement
{
}
