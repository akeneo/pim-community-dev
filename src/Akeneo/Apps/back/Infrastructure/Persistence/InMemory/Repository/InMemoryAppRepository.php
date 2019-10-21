<?php

declare(strict_types=1);

namespace Akeneo\Apps\Infrastructure\Persistence\InMemory\Repository;

use Akeneo\Apps\Domain\Model\Read\App as ReadApp;
use Akeneo\Apps\Domain\Model\ValueObject\AppId;
use Akeneo\Apps\Domain\Model\Write\App as WriteApp;
use Akeneo\Apps\Domain\Persistence\Repository\AppRepository;
use Ramsey\Uuid\Uuid;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InMemoryAppRepository implements AppRepository
{
    private $dataRows = [];

    public function generateId(): AppId
    {
        return new AppId(Uuid::uuid4()->toString());
    }

    public function create(WriteApp $app): void
    {
        $this->dataRows[(string) $app->code()] = [
            'code' => (string) $app->code(),
            'label' => (string) $app->label(),
            'flow_type' => (string) $app->flowType(),
        ];
    }

    public function fetchAll(): array
    {
        $apps = [];
        foreach ($this->dataRows as $dataRow) {
            $apps[] = new ReadApp($dataRow['code'], $dataRow['label'], $dataRow['flow_type']);
        }

        return $apps;
    }

    public function findOneByCode(string $code): ?ReadApp
    {
        if (isset($this->dataRows[$code])) {
            $dataRow = $this->dataRows[$code];
            return new ReadApp(
                $dataRow['code'],
                $dataRow['label'],
                $dataRow['flow_type']
            );
        }
    }

    public function count(): int
    {
        return count($this->dataRows);
    }
}
