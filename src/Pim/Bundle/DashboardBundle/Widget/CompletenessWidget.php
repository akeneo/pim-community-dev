<?php

namespace Pim\Bundle\DashboardBundle\Widget;

use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Enrich\FollowUp\Query\GetCompletenessPerChannelAndLocaleInterface;

/**
 * Widget to display completeness of products over channels and locales
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessWidget implements WidgetInterface
{
    /** @var UserContext */
    protected $userContext;

    /** @var GetCompletenessPerChannelAndLocaleInterface $completenessWidgetQuery */
    protected $completenessWidgetQuery;

    public function __construct(
        UserContext $userContext,
        GetCompletenessPerChannelAndLocaleInterface $completenessWidgetQuery
    ) {
        $this->userContext = $userContext;
        $this->completenessWidgetQuery = $completenessWidgetQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): string
    {
        return 'completeness';
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate(): string
    {
        return 'PimDashboardBundle:Widget:completeness.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters(): array
    {
        return [];
    }

    public function getData(): array
    {
        $translationLocaleCode = $this->userContext->getCurrentLocaleCode();
        $result = $this->completenessWidgetQuery->fetch($translationLocaleCode);

        return $result->toArray();
    }
}
