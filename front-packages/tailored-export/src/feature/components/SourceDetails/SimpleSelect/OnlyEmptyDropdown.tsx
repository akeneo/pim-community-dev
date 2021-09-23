import React from 'react';
import {Dropdown, SwitcherButton, useBooleanState} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

type OnlyEmptyDropdownProps = {
  value: boolean;
  onChange: (updatedValue: boolean) => void;
};

const OnlyEmptyDropdown = ({value, onChange}: OnlyEmptyDropdownProps) => {
  const translate = useTranslate();
  const [isDropdownOpen, open, close] = useBooleanState();

  const handleChange = (updatedValue: boolean) => {
    onChange(updatedValue);
    close();
  };

  return (
    <Dropdown>
      <SwitcherButton
        label={translate(
          'akeneo.tailored_export.column_details.sources.operation.replacement.modal.filters.only_mapped.label'
        )}
        onClick={open}
      >
        {value
          ? translate(
              'akeneo.tailored_export.column_details.sources.operation.replacement.modal.filters.only_mapped.yes'
            )
          : translate(
              'akeneo.tailored_export.column_details.sources.operation.replacement.modal.filters.only_mapped.no'
            )}
      </SwitcherButton>
      {isDropdownOpen && (
        <Dropdown.Overlay verticalPosition="down" onClose={close}>
          <Dropdown.ItemCollection>
            <Dropdown.Item onClick={() => handleChange(false)}>
              {translate(
                'akeneo.tailored_export.column_details.sources.operation.replacement.modal.filters.only_mapped.no'
              )}
            </Dropdown.Item>
            <Dropdown.Item onClick={() => handleChange(true)}>
              {translate(
                'akeneo.tailored_export.column_details.sources.operation.replacement.modal.filters.only_mapped.yes'
              )}
            </Dropdown.Item>
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export {OnlyEmptyDropdown};
