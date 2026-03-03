import React, {FC} from 'react';
import {FamilyCode} from '../../models';

type FamiliesSelectorProps = {
  familyCodes: FamilyCode[];
  onChange: (familyCodes: FamilyCode[]) => void;
};

const FamiliesSelector: FC<FamiliesSelectorProps> = ({familyCodes, onChange}) => {
  const handleChange = () => onChange(['shirts']);

  return (
    <>
      FamiliesSelectorMock
      <div>{JSON.stringify(familyCodes)}</div>
      <button onClick={handleChange}>Set shirts</button>
    </>
  );
};

export {FamiliesSelector};
