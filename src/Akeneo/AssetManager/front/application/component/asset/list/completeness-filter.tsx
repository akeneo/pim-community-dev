import * as React from 'react';
import Dropdown, {DropdownElement} from 'akeneoassetmanager/application/component/app/dropdown';
import {Key} from 'akeneo-design-system';
import __ from 'akeneoassetmanager/tools/translator';

type Props = {
  value: CompletenessValue;
  onChange: (newValue: CompletenessValue) => void;
};

const CompletenessFilterButtonView = ({
  selectedElement,
  onClick,
}: {
  selectedElement: DropdownElement;
  onClick: () => void;
}) => (
  <div
    className="AknActionButton AknActionButton--light AknActionButton--withoutBorder"
    data-identifier={selectedElement.identifier}
    onClick={onClick}
    tabIndex={0}
    onKeyPress={event => {
      if (Key.Space === event.key) onClick();
    }}
  >
    {__('pim_asset_manager.asset.grid.filter.completeness.label')}
    :&nbsp;
    <span className="AknActionButton-highlight" data-identifier={selectedElement.identifier}>
      {selectedElement.label}
    </span>
    <span className="AknActionButton-caret" />
  </div>
);

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

export default class CompletenessFilter extends React.Component<Props> {
  private getCompletenessFilter = (): DropdownElement[] => {
    return [
      {
        identifier: CompletenessValue.All,
        label: __('pim_asset_manager.asset.grid.filter.completeness.all'),
      },
      {
        identifier: CompletenessValue.Yes,
        label: __('pim_asset_manager.asset.grid.filter.completeness.yes'),
      },
      {
        identifier: CompletenessValue.No,
        label: __('pim_asset_manager.asset.grid.filter.completeness.no'),
      },
    ];
  };

  onCompletenessUpdated(event: DropdownElement) {
    this.props.onChange(event.identifier as CompletenessValue);
  }

  render() {
    return (
      <Dropdown
        ItemView={CompletenessFilterItemView}
        ButtonView={CompletenessFilterButtonView}
        label={__('pim_asset_manager.asset.grid.filter.completeness.label')}
        elements={this.getCompletenessFilter()}
        selectedElement={this.props.value}
        onSelectionChange={this.onCompletenessUpdated.bind(this)}
        className="complete-filter"
      />
    );
  }
}
