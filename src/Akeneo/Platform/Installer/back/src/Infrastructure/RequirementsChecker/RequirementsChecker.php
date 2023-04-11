<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Infrastructure\RequirementsChecker;

use Akeneo\Platform\Installer\Application\InstallPim\RequirementsCheckerInterface;
use Akeneo\Platform\Requirements;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RequirementsChecker implements RequirementsCheckerInterface
{
    private readonly Requirements $requirements;

    public function __construct()
    {
        $requirement = new Requirements();
    }

    public function check(): void
    {
        $this->requirements->getMandatoryRequirements();
        $this->requirements->getPhpIniRequirements();
        $this->requirements->getPimRequirements();
        $this->requirements->getRecommendations();

        if (count($this->requirements->getFailedRequirements())) {
            $this->renderTable($collection->getFailedRequirements(), 'Errors', $output);

            throw new \RuntimeException(
                'Some system requirements are not fulfilled. Please check output messages and fix them'
            );
        }
    }
}
