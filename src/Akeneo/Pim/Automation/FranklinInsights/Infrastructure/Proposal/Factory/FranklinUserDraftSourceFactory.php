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


namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Proposal\Factory;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Proposal\ValueObject\ProposalAuthor;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\DraftSource;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class FranklinUserDraftSourceFactory
{
    const SOURCE_CODE = 'franklin';
    const SOURCE_LABEL = 'Franklin Insights';
    const AUTHOR_CODE = ProposalAuthor::USERNAME;
    const AUTHOR_LABEL = 'Franklin Insights';

    public function create(): DraftSource
    {
        return new DraftSource(
            self::SOURCE_CODE,
            self::SOURCE_LABEL,
            self::AUTHOR_CODE,
            self::AUTHOR_LABEL
        );
    }
}
