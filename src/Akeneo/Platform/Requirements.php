<?php

namespace Akeneo\Platform;

use Symfony\Requirements\PhpConfigRequirement;
use Symfony\Requirements\Requirement;
use Symfony\Requirements\SymfonyRequirements;

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
class Requirements extends SymfonyRequirements
{
    /**
     * {@inheritdoc}
     */
    public function __construct(string $baseDirectory)
    {
        parent::__construct($baseDirectory);

        $communityRequirements = new PimRequirements($baseDirectory);

        foreach ($communityRequirements->getRequirements() as $requirement) {
            if (!$requirement->isOptional()) {
                $this->addPimRequirement($requirement);
            } else {
                $this->addRecommendation(
                    $requirement->isFulfilled(),
                    $requirement->getTestMessage(),
                    $requirement->getHelpText()
                );
            }
        }
    }

    /**
     * Adds an Akeneo PIM specific mandatory requirement
     */
    private function addPimRequirement(Requirement $requirement): void
    {
        $this->add($requirement);
    }

    /**
     * Get the list of Akeneo PIM specific requirements
     */
    public function getPimRequirements(): array
    {
        return array_filter($this->getRequirements(), function ($requirement) {
            return $requirement instanceof Requirement;
        });
    }

    /**
     * Get the list of mandatory requirements (all requirements excluding PhpIniRequirement)
     */
    public function getMandatoryRequirements(): array
    {
        return array_filter($this->getRequirements(), function ($requirement) {
            return !($requirement instanceof PhpConfigRequirement) && !($requirement instanceof Requirement);
        });
    }

    /**
     * Get the list of PHP ini requirements
     */
    public function getPhpIniRequirements(): array
    {
        return array_filter($this->getRequirements(), function ($requirement) {
            return $requirement instanceof PhpConfigRequirement;
        });
    }
}
