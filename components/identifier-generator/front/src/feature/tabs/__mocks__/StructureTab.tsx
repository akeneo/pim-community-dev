import React from 'react';
import {Delimiter, PROPERTY_NAMES, Structure} from '../../models';
import initialGenerator from '../../tests/fixtures/initialGenerator';

type StructureTabProps = {
  onStructureChange: (structure: Structure) => void;
  initialStructure: Structure;
  onDelimiterChange: (delimiter: Delimiter) => void;
  delimiter: Delimiter | null;
};

const StructureTab: React.FC<StructureTabProps> = ({
  onStructureChange,
  initialStructure,
  onDelimiterChange,
  delimiter,
}) => {
  const updateFreeText = () => {
    const updatedStructure = [...initialStructure];
    updatedStructure[0] = {type: PROPERTY_NAMES.FREE_TEXT, string: 'Updated string'};
    onStructureChange(updatedStructure);
  };

  const revertFreeText = () => onStructureChange(initialGenerator.structure);
  const deleteFreeText = () => onStructureChange([]);

  const updateDelimiter = () => onDelimiterChange('/');

  return (
    <>
      StructureTabMock
      <div>{JSON.stringify(initialStructure)}</div>
      <div>Delimiter is {delimiter || 'null'}</div>
      <button onClick={updateFreeText}>Update Free Text</button>
      <button onClick={revertFreeText}>Revert Free Text</button>
      <button onClick={deleteFreeText}>Delete Free Text</button>
      <button onClick={updateDelimiter}>Update Delimiter</button>
    </>
  );
};

export {StructureTab};
