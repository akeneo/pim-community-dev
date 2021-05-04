<?php

namespace PimEnterprise\Behat\Context;

use Behat\Gherkin\Node\TableNode;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Context\PimContext;
use Webmozart\Assert\Assert;

class DashboardContext extends PimContext
{
    use SpinCapableTrait;

    /**
     * @Given /^I should see the following proposals on the widget:$/
     *
     * @param TableNode $table
     *
     * @throws \Exception
     */
    public function iShouldSeeTheFollowingProposalsOnTheWidget(TableNode $table)
    {
        $expectedProposals = $table->getHash();

        $proposalsWidget = $this->spin(function () {
            return $this->getCurrentPage()->find('css', 'table.dashboard-widget-proposals-to-review');
        }, 'Proposals widget not found');

        $proposalElements = $proposalsWidget->findAll('css', 'tbody tr');
        $proposals = [];
        foreach ($proposalElements as $proposalElement) {
            $cells = $proposalElement->findAll('css', 'td');
            $proposals[] = [
                'author'  => $cells[1]->getText(),
                'product' => $cells[2]->getText(),
            ];
        }

        Assert::eq($proposals, $expectedProposals, sprintf('Failed to find the following proposals "%s"', print_r($expectedProposals, true)));
    }

    /**
     * Get the channel completeness ratio inside the completeness widget
     *
     * @param string $channel
     *
     * @return string
     */
    public function getChannelCompleteness($channel)
    {
        $completenessWidget = $this->getElementOnCurrentPage('Completeness Widget');

        return $completenessWidget->getChannelCompleteness($channel);
    }

    /**
     * Get the localized channel completeness ratio inside the completeness widget
     *
     * @param string $channel
     * @param string $locale
     *
     * @return string
     */
    public function getLocalizedChannelCompleteness($channel, $locale)
    {
        $completenessWidget = $this->getElementOnCurrentPage('Completeness Widget');

        return $completenessWidget->getLocalizedChannelCompleteness($channel, $locale);
    }
}
