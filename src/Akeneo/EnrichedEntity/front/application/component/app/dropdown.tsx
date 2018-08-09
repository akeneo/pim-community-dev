import * as React from 'react';

export interface DropdownElement {
  identifier: string;
  label: string;
  original?: any;
}

const DefaultButtonView = ({
  selectedElement,
  onClick,
}: {
  label: string;
  selectedElement: DropdownElement;
  onClick: () => void;
}) => (
  <div
    className="AknButton"
    tabIndex={0}
    data-selected={selectedElement.identifier}
    onClick={() => onClick()}
    onKeyPress={event => {
      if (' ' === event.key) onClick();
    }}
    aria-label={selectedElement.label}
  >
    <span className="AknActionButton-highlight">{selectedElement.label}</span>
    <span className="AknActionButton-caret" />
  </div>
);

const DefaultItemView = ({
  element,
  isActive,
  onClick,
}: {
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
        if (' ' === event.key) onClick(element);
      }}
      tabIndex={0}
    >
      {element.label}
    </div>
  );
};

interface Props {
  elements: DropdownElement[];
  selectedElement: string;
  ButtonView?: (
    {label, selectedElement, onClick}: {label: string; selectedElement: DropdownElement; onClick: () => void}
  ) => JSX.Element;
  ItemView?: (
    {
      element,
      isActive,
      onClick,
    }: {element: DropdownElement; isActive: boolean; onClick: (element: DropdownElement) => void}
  ) => JSX.Element;
  label: string;
  className?: string;
  onSelectionChange: (element: DropdownElement) => void;
}

class Dropdown extends React.Component<Props, {isOpen: boolean; selectedElement: string}> {
  state = {
    isOpen: false,
    selectedElement: this.props.selectedElement,
  };

  componentWillReceiveProps(nextProps: Props) {
    this.setState({selectedElement: nextProps.selectedElement});
  }

  open() {
    this.setState({isOpen: true});
  }

  elementSelected(element: DropdownElement) {
    this.setState({isOpen: false});
    if (element.identifier !== this.state.selectedElement) {
      this.setState({selectedElement: element.identifier});
      this.props.onSelectionChange(element);
    }
  }

  close() {
    this.setState({isOpen: false});
  }

  getElement(identifier: string) {
    const searchedElement: DropdownElement | undefined = this.props.elements.find(
      (element: DropdownElement) => element.identifier === identifier
    );

    return undefined === searchedElement ? {identifier: identifier, label: identifier} : searchedElement;
  }

  render() {
    if (null === this.state) {
      return !null;
    }

    const openClass = this.state.isOpen ? 'AknDropdown-menu--open' : '';
    const dropdownButton = (selectedElement: string, label: string) => {
      const Button = undefined !== this.props.ButtonView ? this.props.ButtonView : DefaultButtonView;

      return <Button label={label} selectedElement={this.getElement(selectedElement)} onClick={this.open.bind(this)} />;
    };

    const ElementViews = this.props.elements.map((element: DropdownElement) => {
      const View = undefined !== this.props.ItemView ? this.props.ItemView : DefaultItemView;

      return (
        <View
          key={element.identifier}
          element={element}
          onClick={(element: DropdownElement) => this.elementSelected(element)}
          isActive={element.identifier === this.state.selectedElement}
        />
      );
    });

    return (
      <div className={`AknDropdown ${undefined !== this.props.className ? this.props.className : ''}`}>
        {this.state.isOpen ? <div className="AknDropdown-mask" onClick={this.close.bind(this)} /> : null}
        {dropdownButton(this.state.selectedElement, this.props.label)}
        <div className={'AknDropdown-menu ' + openClass}>
          <div className="AknDropdown-menuTitle">{this.props.label}</div>
          {ElementViews}
        </div>
      </div>
    );
  }
}

export default Dropdown;
