import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import {Key} from 'akeneo-design-system';
import {SidebarLabel} from 'akeneoassetmanager/application/component/app/sidebar';

export interface DropdownMenuElement {
  code: string;
  label: SidebarLabel;
}

const DefaultButtonView = ({onClick}: {onClick: () => void}) => {
  return (
    <React.Fragment>
      <div
        className="AknSecondaryActions-button AknSecondaryActions-button--rotated"
        onClick={() => onClick()}
        onKeyPress={(event: React.KeyboardEvent<HTMLInputElement>) => {
          if (Key.Space === event.key) onClick();
        }}
        tabIndex={0}
      />
    </React.Fragment>
  );
};

const DefaultItemView = ({
  element,
  onClick,
}: {
  element: DropdownMenuElement;
  onClick: (element: DropdownMenuElement) => void;
}) => {
  return (
    <React.Fragment>
      <div
        key={element.code}
        className="AknDropdown-menuLink navigation-link"
        data-tab={element.code}
        onClick={() => onClick(element)}
        onKeyPress={(event: React.KeyboardEvent<HTMLInputElement>) => {
          if (Key.Space === event.key) onClick(element);
        }}
        tabIndex={0}
      >
        <span>{'string' === typeof element.label ? __(element.label) : <element.label />}</span>
      </div>
    </React.Fragment>
  );
};

interface Props {
  elements: DropdownMenuElement[];
  selectedElement: string;
  ButtonView?: ({onClick}: {onClick: (element: DropdownMenuElement) => void}) => JSX.Element;
  ItemView?: ({
    element,
    onClick,
  }: {
    element: DropdownMenuElement;
    onClick: (element: DropdownMenuElement) => void;
  }) => JSX.Element;
  label: string;
  className?: string;
  onSelectionChange: (element: DropdownMenuElement) => void;
}

interface State {
  isOpen: boolean;
}

class DropdownMenu extends React.Component<Props, State> {
  state = {
    isOpen: false,
  };

  open() {
    this.setState({isOpen: true});
  }

  elementSelected(element: DropdownMenuElement) {
    this.close();
    this.props.onSelectionChange(element);
  }

  close() {
    this.setState({isOpen: false});
  }

  getDropdownButton() {
    const Button = undefined !== this.props.ButtonView ? this.props.ButtonView : DefaultButtonView;

    return <Button onClick={this.open.bind(this)} />;
  }

  getElementViews() {
    return this.props.elements.map((element: DropdownMenuElement) => {
      const View = undefined !== this.props.ItemView ? this.props.ItemView : DefaultItemView;

      return (
        <View
          key={element.code}
          element={element}
          onClick={(element: DropdownMenuElement) => this.elementSelected(element)}
        />
      );
    });
  }

  render() {
    const openClass = this.state.isOpen ? 'AknDropdown-menu--open' : '';
    const ElementViews = this.getElementViews();
    const DropdownButton = this.getDropdownButton();

    return (
      <div
        className={`AknSecondaryActions AknDropdown ${undefined !== this.props.className ? this.props.className : ''}`}
      >
        {this.state.isOpen ? <div className="AknDropdown-mask" onClick={this.close.bind(this)} /> : null}
        {DropdownButton}
        <div className={`AknDropdown-menu ${openClass}`}>
          <div className="AknDropdown-menuTitle">{this.props.label}</div>
          {ElementViews}
        </div>
      </div>
    );
  }
}

export default DropdownMenu;
