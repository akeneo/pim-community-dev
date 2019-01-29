import * as React from 'react';
import __ from 'akeneoreferenceentity/tools/translator';
import Key from 'akeneoreferenceentity/tools/key';

export interface DropdownMenuElement {
  code: string;
  label: string;
}

const DefaultItemView = ({
  element,
  onClick,
}: {
  element: DropdownMenuElement;
  onClick: (event: React.MouseEvent<HTMLSpanElement> | React.KeyboardEvent<HTMLSpanElement>) => void;
}) => {
  return (
    <div
      key={element.code}
      className="AknDropdown-menuLink navigation-link"
      data-tab={element.code}
      onClick={onClick}
      onKeyPress={(event: React.KeyboardEvent<HTMLInputElement>) => {
        if (Key.Space === event.key) onClick(event);
      }}
      tabIndex={0}
    >
      <span>{__(element.label)}</span>
    </div>
  );
};

interface Props {
  elements: DropdownMenuElement[];
  selectedElement: string;
  ItemView?: (
    {
      element,
      onClick,
    }: {element: DropdownMenuElement; onClick: (event: React.MouseEvent<HTMLSpanElement> | React.KeyboardEvent<HTMLSpanElement>) => void}
  ) => JSX.Element;
  label: string;
  className?: string;
  onSelectionChange: (event: React.MouseEvent<HTMLSpanElement> | React.KeyboardEvent<HTMLSpanElement>) => void;
}

class DropdownMenu extends React.Component<Props> {
  getElementViews() {
    return this.props.elements.map((element: DropdownMenuElement) => {
      const View = undefined !== this.props.ItemView ? this.props.ItemView : DefaultItemView;

      return (
        <View
          key={element.code}
          element={element}
          onClick={this.props.onSelectionChange}
        />
      );
    })
  }
  render() {
    const ElementViews = this.getElementViews();

    return (
      <div className={`AknSecondaryActions AknDropdown ${undefined !== this.props.className ? this.props.className : ''}`}>
        <div
          className="AknSecondaryActions-button AknSecondaryActions-button--rotated dropdown-button"
          data-toggle="dropdown"
          tabIndex={0}
        ></div>
        <div className="AknDropdown-menu">
          <div className="AknDropdown-menuTitle">{this.props.label}</div>
          {ElementViews}
        </div>
      </div>
    );
  }
}

export default DropdownMenu;
