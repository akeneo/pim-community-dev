import React from 'react';
import {PROPERTY_NAMES, Structure} from '../../models';
import {initialGenerator} from '../../tests/fixtures/initialGenerator';

type StructureTabProps = {
  onStructureChange: (structure: Structure) => void;
  initialStructure: Structure;
};

const StructureTab: React.FC<StructureTabProps> = ({onStructureChange, initialStructure}) => {
  const updateFreeText = () => {
    const updatedStructure = [...initialStructure];
    updatedStructure[0] = {type: PROPERTY_NAMES.FREE_TEXT, string: 'Updated string'};
    onStructureChange(updatedStructure);
  };

  const revertFreeText = () => onStructureChange(initialGenerator.structure);

  return (
    <>
      StructureTabMock
      <div>{JSON.stringify(initialStructure)}</div>
      <button onClick={updateFreeText}>Update Free Text</button>
      <button onClick={revertFreeText}>Revert Free Text</button>
    </>
  );
};

export {StructureTab};
