<?php

declare(strict_types=1);

namespace Akeneo\Apps\Infrastructure\Persistence\InMemory\Repository;

use Akeneo\Apps\Domain\Model\ValueObject\ClientId;
use Akeneo\Apps\Domain\Model\ValueObject\UserId;
use Akeneo\Apps\Domain\Model\Write\App;
use Akeneo\Apps\Domain\Persistence\Repository\AppRepository;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InMemoryAppRepository implements AppRepository
{
    public $dataRows = [];

    public function create(App $app): void
    {
        $this->dataRows[(string) $app->code()] = [
            'code' => (string) $app->code(),
            'label' => (string) $app->label(),
            'flow_type' => (string) $app->flowType(),
            'client_id' => $app->clientId()->id(),
            'user_id' => $app->userId()->id(),
            'random_id' => uniqid(),
            'secret' => uniqid(),
        ];
    }

    public function findOneByCode(string $code): ?App
    {
        if (!isset($this->dataRows[$code])) {
            return null;
        }
        $dataRow = $this->dataRows[$code];

        return new App(
            $dataRow['code'],
            $dataRow['label'],
            $dataRow['flow_type'],
            new ClientId($dataRow['client_id']),
            new UserId($dataRow['user_id'])
        );
    }

    public function update(App $app): void
    {
        if (!isset($this->dataRows[(string) $app->code()])) {
            throw new \LogicException(sprintf('App "%s" never persisted!', (string) $app->code()));
        }

        $this->dataRows[(string) $app->code()]['label'] = (string) $app->label();
        $this->dataRows[(string) $app->code()]['flow_type'] = (string) $app->flowType();
    }

    public function count(): int
    {
        return count($this->dataRows);
    }
}
