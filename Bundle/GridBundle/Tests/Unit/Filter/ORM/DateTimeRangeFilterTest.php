<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Filter\ORM;

use Oro\Bundle\GridBundle\Filter\ORM\DateTimeRangeFilter;
use Oro\Bundle\FilterBundle\Form\Type\Filter\DateTimeRangeFilterType;

class DateTimeRangeFilterTest extends AbstractDateFilterTest
{
    /**
     * @return DateTimeRangeFilter
     */
    protected function createTestFilter()
    {
        return new DateTimeRangeFilter($this->getTranslatorMock());
    }

    public function testGetDefaultOptions()
    {
        $this->assertEquals(array('form_type' => DateTimeRangeFilterType::NAME), $this->model->getDefaultOptions());
    }

    /**
     * @param \DateTime $dateTime
     * @return string
     */
    protected function dateTimeToString(\DateTime $dateTime)
    {
        return $dateTime->format(DateTimeRangeFilter::DATETIME_FORMAT);
    }
}
