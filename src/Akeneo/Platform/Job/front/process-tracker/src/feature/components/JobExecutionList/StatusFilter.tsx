import React from 'react';
import {Checkbox, Dropdown, SwitcherButton, useBooleanState} from 'akeneo-design-system';
import {Translate, useTranslate} from '@akeneo-pim-community/shared';
import {AVAILABLE_JOB_STATUSES, JobStatus} from '../../models';

const getStatusFilterValueLabel = (translate: Translate, statusFilterValue: JobStatus[]): string => {
  switch (statusFilterValue.length) {
    case 0:
      return translate('akeneo_job_process_tracker.status_filter.all');
    case 1:
      return translate(`akeneo_job_process_tracker.status_filter.${statusFilterValue[0].toLowerCase()}`);
    default:
      return translate('pim_common.selected', {itemsCount: statusFilterValue.length}, statusFilterValue.length);
  }
};

type StatusFilterProps = {
  statusFilterValue: JobStatus[];
  onStatusFilterChange: (statusFilterValue: JobStatus[]) => void;
};

const StatusFilter = ({statusFilterValue, onStatusFilterChange}: StatusFilterProps) => {
  const translate = useTranslate();
  const [isDropdownOpen, openDropdown, closeDropdown] = useBooleanState();

  const handleStatusToggle = (status: JobStatus) => () => {
    if (statusFilterValue.includes(status)) {
      onStatusFilterChange(statusFilterValue.filter(currentStatus => currentStatus !== status));
    } else {
      onStatusFilterChange([...statusFilterValue, status]);
    }
  };

  return (
    <Dropdown>
      <SwitcherButton label={translate('akeneo_job_process_tracker.status_filter.label')} onClick={openDropdown}>
        {getStatusFilterValueLabel(translate, statusFilterValue)}
      </SwitcherButton>
      {isDropdownOpen && (
        <Dropdown.Overlay onClose={closeDropdown}>
          <Dropdown.Header>
            <Dropdown.Title>{translate('akeneo_job_process_tracker.status_filter.label')}</Dropdown.Title>
          </Dropdown.Header>
          <Dropdown.ItemCollection>
            <Dropdown.Item>
              <Checkbox checked={0 === statusFilterValue.length} onChange={() => onStatusFilterChange([])} />
              {translate('akeneo_job_process_tracker.status_filter.all')}
            </Dropdown.Item>
            {AVAILABLE_JOB_STATUSES.map(status => (
              <Dropdown.Item key={status}>
                <Checkbox checked={statusFilterValue.includes(status)} onChange={handleStatusToggle(status)} />
                {translate(`akeneo_job_process_tracker.status_filter.${status.toLowerCase()}`)}
              </Dropdown.Item>
            ))}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export {StatusFilter};
