import React from 'react';
import {FreeText as FreeTextModel, PropertyWithIdentifier} from '../../models';
import {BlockButton, RowIcon} from 'akeneo-design-system';

type FreeTextProps = {
  freeTextProperty: FreeTextModel & PropertyWithIdentifier;
  onClick: (id: string) => void;
};

const FreeText: React.FC<FreeTextProps> = ({freeTextProperty, onClick}) => {
  const updateSelectedProperty = () => {
    onClick(freeTextProperty.id);
  };

  return (
    <BlockButton icon={<RowIcon />} onClick={updateSelectedProperty}>
      <RowIcon /> {freeTextProperty.string}
    </BlockButton>
  );
};

export {FreeText};
