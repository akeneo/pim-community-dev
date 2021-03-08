import React from 'react';
import Dropdown, {DropdownElement} from 'akeneoassetmanager/application/component/app/dropdown';
import {Key} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

type CompletenessFilterProps = {
  value: CompletenessValue;
  onChange: (newValue: CompletenessValue) => void;
};

const CompletenessFilterButtonView = ({
  selectedElement,
  onClick,
}: {
  selectedElement: DropdownElement;
  onClick: () => void;
}) => {
  const translate = useTranslate();

  return (
    <div
      className="AknActionButton AknActionButton--light AknActionButton--withoutBorder"
      data-identifier={selectedElement.identifier}
      onClick={onClick}
      tabIndex={0}
      onKeyPress={event => {
        if (Key.Space === event.key) onClick();
      }}
    >
      {translate('pim_asset_manager.asset.grid.filter.completeness.label')}
      :&nbsp;
      <span className="AknActionButton-highlight" data-identifier={selectedElement.identifier}>
        {selectedElement.label}
      </span>
      <span className="AknActionButton-caret" />
    </div>
  );
};

const CompletenessFilterItemView = ({
  isOpen,
  element,
  isActive,
  onClick,
}: {
  isOpen: boolean;
  element: DropdownElement;
  isActive: boolean;
  onClick: (element: DropdownElement) => void;
}) => {
  const menuLinkClass = `AknDropdown-menuLink ${isActive ? 'AknDropdown-menuLink--active' : ''}`;

  return (
    <div
      className={menuLinkClass}
      data-identifier={element.identifier}
      onClick={() => onClick(element)}
      onKeyPress={event => {
        if (Key.Space === event.key) onClick(element);
      }}
      tabIndex={isOpen ? 0 : -1}
    >
      <span>{element.label}</span>
    </div>
  );
};

export enum CompletenessValue {
  All = 'all',
  Yes = 'yes',
  No = 'no',
}

const CompletenessFilter = ({value, onChange}: CompletenessFilterProps) => {
  const translate = useTranslate();

  const getCompletenessFilter = (): DropdownElement[] => {
    return [
      {
        identifier: CompletenessValue.All,
        label: translate('pim_asset_manager.asset.grid.filter.completeness.all'),
      },
      {
        identifier: CompletenessValue.Yes,
        label: translate('pim_asset_manager.asset.grid.filter.completeness.yes'),
      },
      {
        identifier: CompletenessValue.No,
        label: translate('pim_asset_manager.asset.grid.filter.completeness.no'),
      },
    ];
  };

  return (
    <Dropdown
      ItemView={CompletenessFilterItemView}
      ButtonView={CompletenessFilterButtonView}
      label={translate('pim_asset_manager.asset.grid.filter.completeness.label')}
      elements={getCompletenessFilter()}
      selectedElement={value}
      onSelectionChange={(event: DropdownElement) => {
        onChange(event.identifier as CompletenessValue);
      }}
      className="complete-filter"
    />
  );
};

export {CompletenessFilter};
