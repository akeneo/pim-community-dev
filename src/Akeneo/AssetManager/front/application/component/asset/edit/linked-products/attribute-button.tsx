import * as React from 'react';
import {Key} from 'akeneo-design-system';
import {DropdownElement} from 'akeneoassetmanager/application/component/app/dropdown';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

interface Props {
  selectedElement: DropdownElement;
  onClick: () => void;
}

export const AttributeButton = ({selectedElement, onClick}: Props) => {
  const translate = useTranslate();
  const handleKeyPress = React.useCallback(
    event => {
      if (Key.Space === event.key) onClick();
    },
    [onClick]
  );

  return (
    <div
      className="AknActionButton AknActionButton--light AknActionButton--withoutBorder"
      data-identifier={selectedElement.identifier}
      onClick={onClick}
      tabIndex={0}
      onKeyPress={handleKeyPress}
    >
      {translate('pim_asset_manager.asset.product.dropdown.attribute')}
      :&nbsp;
      <span className="AknActionButton-highlight" data-identifier={selectedElement.identifier}>
        {selectedElement.label}
      </span>
      <span className="AknActionButton-caret" />
    </div>
  );
};
