import React from 'react';
import {Checkbox, Dropdown, SwitcherButton, useBooleanState} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useJobExecutionTypes} from '../hooks/useJobExecutionTypes';

type JobExecutionTypeFilterProps = {
  selected: string[];
  onChange: (selectedTypes: string[]) => void;
};

const JobExecutionTypeFilter = ({selected, onChange}: JobExecutionTypeFilterProps) => {
  const translate = useTranslate();
  const [isOpen, open, close] = useBooleanState(false);

  let switcherButtonText = '';
  switch (selected.length) {
    case 0:
      switcherButtonText = 'All';
      break;
    case 1:
      switcherButtonText = selected[0];
      break;
    default:
      switcherButtonText = `${selected.length} selected`;
      break;
  }

  const jobExecutionTypes = useJobExecutionTypes();

  return (
    <Dropdown>
      <SwitcherButton onClick={open} label="Types">
        {switcherButtonText}
      </SwitcherButton>
      {isOpen && (
        <Dropdown.Overlay verticalPosition="down" onClose={close}>
          <Dropdown.Header>
            <Dropdown.Title>Types</Dropdown.Title>
          </Dropdown.Header>
          <Dropdown.ItemCollection>
            {jobExecutionTypes &&
              jobExecutionTypes.map(jobExecutionType => (
                <Dropdown.Item key={jobExecutionType}>
                  <Checkbox
                    checked={selected.includes(jobExecutionType)}
                    onChange={checked => {
                      if (checked) {
                        onChange([...selected, jobExecutionType]);
                      } else {
                        onChange(selected.filter(selectedType => selectedType !== jobExecutionType));
                      }
                    }}
                  />
                  {translate(`akeneo_job_process_tracker.job_execution_list.table.filters.type.${jobExecutionType}`)}
                </Dropdown.Item>
              ))}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export {JobExecutionTypeFilter};
