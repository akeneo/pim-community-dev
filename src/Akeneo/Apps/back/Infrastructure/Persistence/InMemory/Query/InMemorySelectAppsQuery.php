<?php

declare(strict_types=1);

namespace Akeneo\Apps\Infrastructure\Persistence\InMemory\Query;

use Akeneo\Apps\Domain\Model\Read\App;
use Akeneo\Apps\Domain\Persistence\Query\SelectAppsQuery;
use Akeneo\Apps\Infrastructure\Persistence\InMemory\Repository\InMemoryAppRepository;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InMemorySelectAppsQuery implements SelectAppsQuery
{
    /** @var InMemoryAppRepository */
    private $appRepository;

    public function __construct(InMemoryAppRepository $appRepository)
    {
        $this->appRepository = $appRepository;
    }

    public function execute(): array
    {
        $apps = [];
        foreach ($this->appRepository->dataRows as $dataRow) {
            $apps[] = new App($dataRow['code'], $dataRow['label'], $dataRow['flow_type'], $dataRow['image']);
        }

        return $apps;
    }
}
