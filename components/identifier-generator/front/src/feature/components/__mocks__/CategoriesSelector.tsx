import React, {FC} from 'react';
import {CategoryCode} from '@akeneo-pim-community/shared';

type CategoriesSelectorProps = {
  categoryCodes: CategoryCode[];
  onChange: (categoryCodes: CategoryCode[]) => void;
};

const CategoriesSelector: FC<CategoriesSelectorProps> = ({categoryCodes, onChange}) => {
  const handleClick = () => {
    onChange(['categoryB']);
  };

  return (
    <>
      <div>CategoriesSelectorMock</div>
      <div>{JSON.stringify(categoryCodes)}</div>
      <button onClick={handleClick}>Set categoryB</button>
    </>
  );
};

export {CategoriesSelector};
