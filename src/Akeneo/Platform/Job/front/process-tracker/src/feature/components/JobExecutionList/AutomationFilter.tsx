import React from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Checkbox, Dropdown, SwitcherButton, useBooleanState} from 'akeneo-design-system';

type AutomationFilterProps = {
  automationFilterValue: null | boolean;
  onAutomationFilterChange: (automationFilterValue: null | boolean) => void;
};

type AutomationFilterValue = {
  name: string;
  value: null | boolean;
};

const automationFilterValues: Array<AutomationFilterValue> = [
  {name: 'all', value: null},
  {name: 'yes', value: true},
  {name: 'no', value: false},
];

const AutomationFilter = ({automationFilterValue, onAutomationFilterChange}: AutomationFilterProps) => {
  const translate = useTranslate();
  const [isDropdownOpen, openDropdown, closeDropdown] = useBooleanState();

  return (
    <Dropdown>
      <SwitcherButton label={translate('akeneo_job_process_tracker.automation_filter.label')} onClick={openDropdown}>
        {automationFilterValues.map(
          (item: AutomationFilterValue) =>
            item.value === automationFilterValue &&
            translate(`akeneo_job_process_tracker.automation_filter.${item.name}`)
        )}
      </SwitcherButton>
      {isDropdownOpen && (
        <Dropdown.Overlay onClose={closeDropdown}>
          <Dropdown.Header>
            <Dropdown.Title>{translate('akeneo_job_process_tracker.automation_filter.label')}</Dropdown.Title>
          </Dropdown.Header>
          <Dropdown.ItemCollection>
            {automationFilterValues.map((item: AutomationFilterValue) => (
              <Dropdown.Item key={item.name}>
                <Checkbox
                  checked={item.value === automationFilterValue}
                  onChange={() => onAutomationFilterChange(item.value)}
                />
                {translate(`akeneo_job_process_tracker.automation_filter.${item.name}`)}
              </Dropdown.Item>
            ))}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export {AutomationFilter};
