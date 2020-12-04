import React, {FunctionComponent} from 'react';
import {DATA_QUALITY_INSIGHTS_DASHBOARD_CHANGE_TIME_PERIOD} from '../../../constant';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

const handleTimePeriodChange = (value: string) => {
  window.dispatchEvent(
    new CustomEvent(DATA_QUALITY_INSIGHTS_DASHBOARD_CHANGE_TIME_PERIOD, {
      detail: {
        timePeriod: value,
      },
    })
  );
};

interface TimePeriodFilterProps {
  timePeriod: string;
}

const TimePeriodFilter: FunctionComponent<TimePeriodFilterProps> = ({timePeriod}) => {
  const translate = useTranslate();
  return (
    <div className="AknFilterBox-filterContainer AknDropdown">
      <button
        type="button"
        className="AknFilterBox-filter AknActionButton AknActionButton--withoutBorder"
        data-toggle="dropdown"
      >
        <span className="AknFilterBox-filterLabel">
          {translate('akeneo_data_quality_insights.dqi_dashboard.time_period.label')}
        </span>
        <span className="AknActionButton-highlight">
          <span>{translate(`akeneo_data_quality_insights.dqi_dashboard.time_period.${timePeriod}`)}</span>
          <span className="AknActionButton-caret" />
        </span>
      </button>
      <ul className="AknDropdown-menu">
        <div className="AknDropdown-menuTitle">
          {translate('akeneo_data_quality_insights.dqi_dashboard.time_period.daily')}
        </div>
        <li>
          <a
            className={`AknDropdown-menuLink ${timePeriod === 'daily' ? 'AknDropdown-menuLink--active' : ''}`}
            data-label={translate('akeneo_data_quality_insights.dqi_dashboard.time_period.daily')}
            onClick={() => handleTimePeriodChange('daily')}
          >
            {translate('akeneo_data_quality_insights.dqi_dashboard.time_period.daily')}
          </a>
        </li>
        <li>
          <a
            className={`AknDropdown-menuLink ${timePeriod === 'weekly' ? 'AknDropdown-menuLink--active' : ''}`}
            data-label={translate('akeneo_data_quality_insights.dqi_dashboard.time_period.weekly')}
            onClick={() => handleTimePeriodChange('weekly')}
          >
            {translate('akeneo_data_quality_insights.dqi_dashboard.time_period.weekly')}
          </a>
        </li>
        <li>
          <a
            className={`AknDropdown-menuLink ${timePeriod === 'monthly' ? 'AknDropdown-menuLink--active' : ''}`}
            data-label={translate('akeneo_data_quality_insights.dqi_dashboard.time_period.monthly')}
            onClick={() => handleTimePeriodChange('monthly')}
          >
            {translate('akeneo_data_quality_insights.dqi_dashboard.time_period.monthly')}
          </a>
        </li>
      </ul>
    </div>
  );
};

export {TimePeriodFilter};
