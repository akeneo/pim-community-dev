import * as React from 'react';
import {Display} from 'pimfront/product-grid/domain/event/display';
import Dropdown from 'pimfront/app/application/component/dropdown';
import * as trans from 'pimenrich/lib/translator';

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
          label: trans.get('grid.display_selector.gallery'),
        },
        {
          identifier: Display.List,
          label: trans.get('grid.display_selector.list'),
        },
      ]}
      label={trans.get('grid.display_selector.label')}
      selectedElement={displayType}
      onSelectionChange={(selection: Display) => {
        onDisplayChange(selection);
      }}
    />
  );
};
