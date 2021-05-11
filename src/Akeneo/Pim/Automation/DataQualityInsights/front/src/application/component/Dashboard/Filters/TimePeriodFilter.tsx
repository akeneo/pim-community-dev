import React, {FunctionComponent} from 'react';
import {Dropdown, SwitcherButton, useBooleanState} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {DATA_QUALITY_INSIGHTS_DASHBOARD_CHANGE_TIME_PERIOD} from '../../../constant';

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
  const [isDropdownOpen, openDropdown, closeDropdown] = useBooleanState();

  const handleItemClick = (timePeriod: string) => () => {
    handleTimePeriodChange(timePeriod);
    closeDropdown();
  };

  return (
    <Dropdown>
      <SwitcherButton
        label={translate('akeneo_data_quality_insights.dqi_dashboard.time_period.label')}
        onClick={openDropdown}
      >
        {translate(`akeneo_data_quality_insights.dqi_dashboard.time_period.${timePeriod}`)}
      </SwitcherButton>
      {isDropdownOpen && (
        <Dropdown.Overlay onClose={closeDropdown}>
          <Dropdown.Header>
            <Dropdown.Title>{translate('akeneo_data_quality_insights.dqi_dashboard.time_period.label')}</Dropdown.Title>
          </Dropdown.Header>
          <Dropdown.ItemCollection>
            <Dropdown.Item onClick={handleItemClick('daily')}>
              {translate('akeneo_data_quality_insights.dqi_dashboard.time_period.daily')}
            </Dropdown.Item>
            <Dropdown.Item onClick={handleItemClick('weekly')}>
              {translate('akeneo_data_quality_insights.dqi_dashboard.time_period.weekly')}
            </Dropdown.Item>
            <Dropdown.Item onClick={handleItemClick('monthly')}>
              {translate('akeneo_data_quality_insights.dqi_dashboard.time_period.monthly')}
            </Dropdown.Item>
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export {TimePeriodFilter};
