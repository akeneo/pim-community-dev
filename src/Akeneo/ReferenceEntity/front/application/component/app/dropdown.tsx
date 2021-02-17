import * as React from 'react';
import Key from 'akeneoreferenceentity/tools/key';

export interface DropdownElement {
  identifier: string;
  label: string;
  original?: any;
}

const DefaultButtonView = ({
  selectedElement,
  onClick,
  allowEmpty = false,
  placeholder = null,
  readOnly,
}: {
  open: boolean;
  selectedElement: DropdownElement;
  onClick: () => void;
  allowEmpty?: boolean;
  placeholder?: string | null;
  readOnly: boolean;
}) => {
  const hasPlaceholder = allowEmpty && placeholder && selectedElement.identifier === null;
  const highlight = hasPlaceholder ? placeholder : selectedElement.label;

  return (
    <React.Fragment>
      <div
        className="AknButton"
        tabIndex={0}
        data-selected={hasPlaceholder ? '' : selectedElement.identifier}
        onClick={() => onClick()}
        onKeyPress={event => {
          if (Key.Space === event.key) onClick();
        }}
        aria-label={selectedElement.label}
      >
        <span className="AknActionButton-highlight">{highlight}</span>
        {!readOnly ? <span className="AknActionButton-caret" /> : null}
      </div>
    </React.Fragment>
  );
};

const DefaultItemView = ({
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
      {element.label}
    </div>
  );
};

interface Props {
  elements: DropdownElement[];
  selectedElement: string;
  ButtonView?: ({
    label,
    selectedElement,
    onClick,
  }: {
    label: string;
    selectedElement: DropdownElement;
    onClick: () => void;
  }) => JSX.Element;
  ItemView?: ({
    isOpen,
    element,
    isActive,
    onClick,
  }: {
    isOpen: boolean;
    element: DropdownElement;
    isActive: boolean;
    onClick: (element: DropdownElement) => void;
  }) => JSX.Element;
  label: string;
  className?: string;
  onSelectionChange: (element: DropdownElement) => void;
  allowEmpty?: boolean;
  placeholder?: string;
  readOnly?: boolean;
  isOpenUp?: boolean;
  isOpenLeft?: boolean;
}

interface State {
  isOpen: boolean;
  selectedElement: string;
}

//TODO Use DSM Dropdown
class Dropdown extends React.Component<Props, State> {
  state = {
    isOpen: false,
    selectedElement: this.props.selectedElement,
  };

  static getDerivedStateFromProps(props: Props, state: State) {
    return {...state, selectedElement: props.selectedElement};
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
    const openClass = this.state.isOpen ? 'AknDropdown-menu--open' : '';
    const dropdownTopClass = this.props.isOpenUp ? 'AknDropdown-menu--top' : '';
    const dropdownLeftClass = this.props.isOpenLeft ? 'AknDropdown-menu--right' : '';
    const dropdownButton = (
      selectedElement: string,
      label: string,
      allowEmpty?: boolean,
      placeholder?: string,
      readOnly?: boolean
    ) => {
      const Button: any = undefined !== this.props.ButtonView ? this.props.ButtonView : DefaultButtonView;

      return (
        <Button
          label={label}
          selectedElement={this.getElement(selectedElement)}
          onClick={this.open.bind(this)}
          allowEmpty={allowEmpty}
          placeholder={placeholder}
          readOnly={readOnly}
        />
      );
    };

    const ElementViews = this.props.elements.map((element: DropdownElement) => {
      const View = undefined !== this.props.ItemView ? this.props.ItemView : DefaultItemView;

      return (
        <View
          key={element.identifier}
          element={element}
          onClick={(element: DropdownElement) => this.elementSelected(element)}
          isActive={element.identifier === this.state.selectedElement}
          isOpen={this.state.isOpen}
        />
      );
    });

    return (
      <div className={`AknDropdown ${undefined !== this.props.className ? this.props.className : ''}`}>
        {this.state.isOpen ? <div className="AknDropdown-mask" onClick={this.close.bind(this)} /> : null}
        {dropdownButton(
          this.state.selectedElement,
          this.props.label,
          this.props.allowEmpty,
          this.props.placeholder,
          this.props.readOnly
        )}
        {!this.props.readOnly ? (
          <div
            className={`AknDropdown-menu AknDropdown-menu--heightLimited ${openClass} ${dropdownTopClass} ${dropdownLeftClass}`}
          >
            <div className="AknDropdown-menuTitle">{this.props.label}</div>
            {ElementViews}
          </div>
        ) : null}
      </div>
    );
  }
}

export default Dropdown;
