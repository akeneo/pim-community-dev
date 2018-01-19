import * as React from 'react';

export interface DropdownElement {
  identifier: string;
  label: string;
};

const DefaultButton = ({label}: {label: string}) => (
  <div className="AknActionButton AknActionButton--withoutBorder">
    <span className="AknActionButton-highlight">{label}</span>
    <span className="AknActionButton-caret"></span>
  </div>
);

interface Props {
  elements: DropdownElement[];
  selectedElement: string;
  ButtonView?: (({label}: {label: string}) => React.Component);
  label: string;
  onSelectionChange: (element: string) => void;
}

export default class Dropdown extends React.Component<
  Props,
  {open: boolean, selectedElement: DropdownElement}
> {
  constructor (props: Props) {
    super(props);

    const foundElement: DropdownElement|undefined = props.elements.find(
      ((element: DropdownElement) => element.identifier === props.selectedElement)
    );

    if (undefined === foundElement) {
      throw new Error(`Cannot find element ${props.selectedElement}`);
    }

    this.state = {
      open: false,
      selectedElement: foundElement
    };
  }

  open () {
    this.setState({open: true});
  }

  elementSelected (element: DropdownElement) {
    if (element.identifier !== this.state.selectedElement.identifier) {
      this.setState({selectedElement: element});
      this.props.onSelectionChange(element.identifier);
    }

    this.close();
  }

  close () {
    this.setState({open: false});
  }

  render () {
    const dropdownButton = (label: string) => {
      const Button = undefined !== this.props.ButtonView ? this.props.ButtonView : DefaultButton;

      return <Button label={label} />
    };
    const ElementViews = this.props.elements.map((element: DropdownElement) => {
      const className = `AknDropdown-menuLink ${element.identifier === this.state.selectedElement.identifier ? 'AknDropdown-menuLink--active' : ''}`

      return (
        <div className={className}>
          {element.label}
        </div>
      );
    });

    return (
      <div className="AknDropdown">
        {dropdownButton(this.state.selectedElement.label)}
        <div className="AknDropdown-menu">
            <div className="AknDropdown-menuTitle">{this.props.label}</div>
            {ElementViews}
        </div>
      </div>
    );
  }
}


