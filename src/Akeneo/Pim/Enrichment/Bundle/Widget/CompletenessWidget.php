<?php

namespace Akeneo\Pim\Enrichment\Bundle\Widget;

use Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\FollowUp\GetCompletenessPerChannelAndLocale;
use Akeneo\Platform\Bundle\DashboardBundle\Widget\WidgetInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;

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

    /** @var GetCompletenessPerChannelAndLocale */
    private $completenessWidgetQuery;

    /**
     * @param UserContext $userContext
     * @param GetCompletenessPerChannelAndLocale $completenessWidgetQuery
     */
    public function __construct(
        UserContext $userContext,
        GetCompletenessPerChannelAndLocale $completenessWidgetQuery
    ) {
        $this->userContext      = $userContext;
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
        return 'AkeneoPimEnrichmentBundle:Widget:completeness.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): array
    {
        $translationLocaleCode = $this->userContext->getUiLocaleCode();
        $result = $this->completenessWidgetQuery->fetch($translationLocaleCode);

        return $result->toArray();
    }
}
