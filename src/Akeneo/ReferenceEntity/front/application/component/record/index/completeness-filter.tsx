import * as React from 'react';
import Dropdown, {DropdownElement} from 'akeneoreferenceentity/application/component/app/dropdown';
import Key from 'akeneoreferenceentity/tools/key';

type Props = {
  value: boolean | null;
  onChange: (newValue: boolean | null) => void;
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
  const className = `AknDropdown-menuLink ${isActive ? 'AknDropdown-menuLink--active' : ''}`;
  return (
    <div
      className={className}
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
        label="Completeness"
        elements={this.getCompletenessFilter()}
        selectedElement={this.value}
        onSelectionChange={this.onCompletenessUpdated.bind(this)}
      />
    );
  }
}
