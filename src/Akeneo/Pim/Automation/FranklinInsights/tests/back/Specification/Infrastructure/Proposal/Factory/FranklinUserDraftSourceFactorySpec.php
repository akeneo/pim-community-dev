<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Proposal\Factory;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Proposal\Factory\FranklinUserDraftSourceFactory;
use PhpSpec\ObjectBehavior;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class FranklinUserDraftSourceFactorySpec extends ObjectBehavior
{
    public function it_creates_a_draft_source_from_the_franklin_user()
    {
        $draftSource = $this->create();

        $draftSource->getAuthor()->shouldBe(FranklinUserDraftSourceFactory::AUTHOR_CODE);
        $draftSource->getAuthorLabel()->shouldBe(FranklinUserDraftSourceFactory::AUTHOR_LABEL);
        $draftSource->getSource()->shouldBe(FranklinUserDraftSourceFactory::SOURCE_CODE);
        $draftSource->getSourceLabel()->shouldBe(FranklinUserDraftSourceFactory::SOURCE_LABEL);
    }
}
