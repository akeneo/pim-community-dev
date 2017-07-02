<?php

namespace Pim\Behat\Context\Domain\Enrich;

use PHPUnit\Framework\Assert;
use Pim\Behat\Context\PimContext;

/**
 * A context for managing the grid pagination and size
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GridPaginationContext extends PimContext
{
    /**
     * @param int $num
     *
     * @throws ExpectationException
     *
     * @Then /^the page number should be (\d+)$/
     */
    public function thePageNumberShouldBe($num)
    {
        $pageNumber = $this->getCurrentPage()->getCurrentGrid()->getPageNumber();

        if ($pageNumber !== (int) $num) {
            throw  $this->getMainContext()->createExpectationException(
                sprintf('Expecting page "%s" got "%s" instead', $num, $pageNumber)
            );
        }
    }

    /**
     * @param int $num
     *
     * @Then /^I change the page number to (\d+)$/
     */
    public function iChangeThePageNumber($num)
    {
        $this->getCurrentPage()->getCurrentGrid()->setPageNumber($num);
    }

    /**
     * @param int $size
     *
     * @throws ExpectationException
     *
     * @When /^the page size should be (\d+)$/
     */
    public function thePageSizeShouldBe($size)
    {
        $pageSize = $this->getCurrentPage()->getCurrentGrid()->getPageSize();

        if ($pageSize != $size) {
            throw  $this->getMainContext()->createExpectationException(
                sprintf('Expecting page "%s" got "%s" instead', $size, $pageSize)
            );
        }
    }

    /**
     * @param int $size
     *
     * @When /^I change the page size to (\d+)$/
     */
    public function iChangeThePageSize($size)
    {
        Assert::assertContains($size, [10, 25, 50, 100], 'Only 10, 25, 50 and 100 records per page are available');
        $this->getCurrentPage()->getCurrentGrid()->setPageSize($size);
        $this->wait();
    }
}
