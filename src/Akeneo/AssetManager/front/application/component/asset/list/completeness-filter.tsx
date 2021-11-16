import React from 'react';
import {Dropdown, SwitcherButton, useBooleanState} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

enum CompletenessValue {
  All = 'all',
  Yes = 'yes',
  No = 'no',
}

type CompletenessFilterProps = {
  value: CompletenessValue;
  onChange: (newValue: CompletenessValue) => void;
};

const CompletenessFilter = ({value, onChange}: CompletenessFilterProps) => {
  const translate = useTranslate();
  const [isDropdownOpen, openDropdown, closeDropdown] = useBooleanState();

  const handleChange = (newValue: CompletenessValue) => () => {
    closeDropdown();
    onChange(newValue);
  };

  return (
    <Dropdown>
      <SwitcherButton
        label={translate('pim_asset_manager.asset.grid.filter.completeness.label')}
        onClick={openDropdown}
      >
        {translate(`pim_asset_manager.asset.grid.filter.completeness.${value.toString()}`)}
      </SwitcherButton>
      {isDropdownOpen && (
        <Dropdown.Overlay onClose={closeDropdown}>
          <Dropdown.Header>
            <Dropdown.Title>{translate('pim_asset_manager.asset.grid.filter.completeness.label')}</Dropdown.Title>
          </Dropdown.Header>
          <Dropdown.ItemCollection>
            {Object.keys(CompletenessValue).map((completenessValue: keyof typeof CompletenessValue) => (
              <Dropdown.Item
                key={completenessValue}
                onClick={handleChange(CompletenessValue[completenessValue])}
                isActive={CompletenessValue[completenessValue] === value}
              >
                {translate(
                  `pim_asset_manager.asset.grid.filter.completeness.${CompletenessValue[completenessValue].toString()}`
                )}
              </Dropdown.Item>
            ))}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export {CompletenessFilter, CompletenessValue};
