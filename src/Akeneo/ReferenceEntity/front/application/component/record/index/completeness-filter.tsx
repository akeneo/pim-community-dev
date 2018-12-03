import * as React from 'react';
import Dropdown, {DropdownElement} from 'akeneoreferenceentity/application/component/app/dropdown';
import Key from 'akeneoreferenceentity/tools/key';
import __ from 'akeneoreferenceentity/tools/translator';

type Props = {
  value: boolean | null;
  onChange: (newValue: boolean | null) => void;
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
    {__('Complete')}
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

export default class CompletenessFilter extends React.Component<Props> {
  private value: string;
  private getCompletenessFilter = (): DropdownElement[] => {
    return [
      {
        identifier: 'all',
        label: 'ALL',
        original: 'completeness',
      },
      {
        identifier: 'yes',
        label: 'YES',
        original: 'completeness',
      },
      {
        identifier: 'no',
        label: 'NO',
        original: 'completeness',
      },
    ];
  };

  onCompletenessUpdated(event: DropdownElement) {
    let completenessValue: boolean | null;
    if ('all' === event.identifier) {
      completenessValue = null;
    } else {
      completenessValue = 'yes' === event.identifier ? true : false;
    }

    this.props.onChange(completenessValue);
  }

  render() {
    if (null === this.props.value) {
      this.value = 'all';
    } else {
      this.value = true === this.props.value ? 'yes' : 'no';
    }

    return (
      <Dropdown
        ItemView={CompletenessFilterItemView}
        ButtonView={CompletenessFilterButtonView}
        label={__('Complete')}
        elements={this.getCompletenessFilter()}
        selectedElement={this.value}
        onSelectionChange={this.onCompletenessUpdated.bind(this)}
        className="complete-filter"
      />
    );
  }
}
