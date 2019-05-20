<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\PublicApi\Onboarder;

use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordLabelsByIdentifiersInterface;
use Doctrine\DBAL\Connection;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class FindRecordLabelsByIdentifiers
{
    /** @var Connection */
    private $sqlConnection;

    /** @var FindRecordLabelsByIdentifiersInterface  */
    private $findRecordLabelsByIdentifiers;

    public function __construct(Connection $sqlConnection, FindRecordLabelsByIdentifiersInterface $findRecordLabelsByIdentifiers)
    {
        $this->sqlConnection = $sqlConnection;
        $this->findRecordLabelsByIdentifiers = $findRecordLabelsByIdentifiers;
    }

    /**
     * Find records by their $recordIdentifiers then returns their labels by their record identifier:
     * [
     *      'designer_starck_abcdef123456789' => [
     *          'labels' => [
     *              'fr_FR' => 'Un label',
     *              'en_US' => 'A label'
     *          ],
     *          'code' => 'starck'
     *      ],
     *      'designer_dyson_abcdef123456789' => [
     *          'labels' => [
     *              'fr_FR' => 'Un label',
     *              'en_US' => 'A label'
     *          ],
     *          'code' => 'dyson'
     *      ],
     * ]
     */
    public function find(array $recordIdentifiers): array
    {
        return $this->findRecordLabelsByIdentifiers->find($recordIdentifiers);
    }
}
