import React from 'react';
import {FreeText, Structure} from '../../models';

type StructureTabProps = {
  onStructureChange: (structure: Structure) => void;
  initialStructure: Structure;
}

const StructureTab: React.FC<StructureTabProps> = ({onStructureChange, initialStructure}) => {
  const updateFreeText = () => {
    (initialStructure[0] as FreeText).string = 'Updated string';
    onStructureChange(initialStructure);
  };

  return <>
    StructureTabMock
    <div>{JSON.stringify(initialStructure)}</div>
    <button onClick={updateFreeText}>Update Free Text</button>
  </>;
};

export {StructureTab};
