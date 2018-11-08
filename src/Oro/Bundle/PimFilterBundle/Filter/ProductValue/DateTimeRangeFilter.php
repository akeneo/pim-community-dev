<?php

namespace Oro\Bundle\PimFilterBundle\Filter\ProductValue;

use Akeneo\UserManagement\Bundle\Context\UserContext;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\FilterBundle\Form\Type\Filter\DateRangeFilterType;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Date time filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateTimeRangeFilter extends AbstractDateFilter
{
    const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /** @var UserContext */
    private $userContext;

    public function __construct(FormFactoryInterface $factory, FilterUtility $util, UserContext $userContext)
    {
        parent::__construct($factory, $util);

        $this->userContext = $userContext;
    }

    /**
     * {@inheritdoc}
     *
     * Override to set the time of the DateTime object on-the-fly according to the chosen operator.
     */
    public function parseData($data)
    {
        if (!$this->isValidData($data)) {
            return false;
        }

        $userTimeZone = new \DateTimeZone($this->userContext->getUserTimezone());

        switch ($data['type']) {
            case DateRangeFilterType::TYPE_MORE_THAN:
                $data['value']['start']->setTime(23, 59, 59);
                $data['values']['start'] = $this->applyTimeZone($userTimeZone, $data['value']['start']);
                break;
            case DateRangeFilterType::TYPE_LESS_THAN:
                $data['value']['end']->setTime(0, 0, 0);
                $data['values']['end'] = $this->applyTimeZone($userTimeZone, $data['value']['end']);
                break;
            default:
                if (isset($data['value']['start']) && $data['value']['start'] instanceof \DateTime) {
                    $data['value']['start']->setTime(0, 0, 0);
                    $data['values']['start'] = $this->applyTimeZone($userTimeZone, $data['value']['start']);
                }
                if (isset($data['value']['end']) && $data['value']['end'] instanceof \DateTime) {
                    $data['value']['end']->setTime(23, 59, 59);
                    $data['values']['end'] = $this->applyTimeZone($userTimeZone, $data['value']['end']);
                }
        }

        return parent::parseData($data);
    }

    /**
     * {@inheritdoc}
     */
    protected function getFormType()
    {
        return DateRangeFilterType::class;
    }

    /**
     * @param \DateTimeZone      $userTimeZone
     * @param \DateTimeInterface $dateTime
     *
     * @return \DateTimeInterface
     */
    private function applyTimeZone(\DateTimeZone $userTimeZone, \DateTimeInterface $dateTime): \DateTimeInterface
    {
        $dateTime->setTimezone(new \DateTimeZone('UTC'));
        $diffWithUTC = - ($userTimeZone->getOffset($dateTime) / 3600);
        $dateTime->modify($diffWithUTC . ' hours');

        return $dateTime;
    }
}
