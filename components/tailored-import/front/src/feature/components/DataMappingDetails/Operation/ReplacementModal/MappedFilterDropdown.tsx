import React from 'react';
import {Dropdown, SwitcherButton, useBooleanState} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

const mappedFilterValues = ['all', 'unmapped', 'mapped'] as const;
type MappedFilterValue = typeof mappedFilterValues[number];

type MappedFilterDropdownProps = {
  value: MappedFilterValue;
  onChange: (updatedValue: MappedFilterValue) => void;
};

const MappedFilterDropdown = ({value, onChange}: MappedFilterDropdownProps) => {
  const translate = useTranslate();
  const [isDropdownOpen, open, close] = useBooleanState();

  const handleChange = (updatedValue: MappedFilterValue) => {
    onChange(updatedValue);
    close();
  };

  return (
    <Dropdown>
      <SwitcherButton
        label={translate('akeneo.tailored_import.data_mapping.operations.replacement.modal.filters.mapped.label')}
        onClick={open}
      >
        {translate(`akeneo.tailored_import.data_mapping.operations.replacement.modal.filters.mapped.${value}`)}
      </SwitcherButton>
      {isDropdownOpen && (
        <Dropdown.Overlay verticalPosition="down" onClose={close}>
          <Dropdown.ItemCollection>
            {mappedFilterValues.map(filterValue => (
              <Dropdown.Item
                key={filterValue}
                isActive={filterValue === value}
                onClick={() => handleChange(filterValue)}
              >
                {translate(
                  `akeneo.tailored_import.data_mapping.operations.replacement.modal.filters.mapped.${filterValue}`
                )}
              </Dropdown.Item>
            ))}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export {MappedFilterDropdown};
export type {MappedFilterValue};
