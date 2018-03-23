import * as React from 'react';
import {Display} from 'pimfront/product-grid/domain/event/display';
import Dropdown, {DropdownElement} from 'pimfront/app/application/component/dropdown';
import __ from 'pimfront/tools/translator';

export default ({
  displayType,
  onDisplayChange,
}: {
  displayType: Display;
  onDisplayChange: (displayType: Display) => void;
}) => {
  return (
    <Dropdown
      elements={[
        {
          identifier: Display.Gallery,
          label: __('grid.display_selector.gallery'),
        },
        {
          identifier: Display.List,
          label: __('grid.display_selector.list'),
        },
      ]}
      className="display-switcher"
      label={__('grid.display_selector.label')}
      selectedElement={displayType}
      onSelectionChange={(selection: DropdownElement) => {
        onDisplayChange(Display.Gallery === selection.identifier ? Display.Gallery : Display.List);
      }}
    />
  );
};
