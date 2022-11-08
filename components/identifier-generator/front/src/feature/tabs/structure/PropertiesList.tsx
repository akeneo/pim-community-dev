import React from 'react';
import {PropertyWithIdentifier} from '../../models';
import {Property} from './Property';
import {StructureWithIdentifiers} from '../Structure';

type PropertiesListProps = {
  structure: StructureWithIdentifiers;
  onChange: (id: string) => void;
};

const PropertiesList: React.FC<PropertiesListProps> = ({structure, onChange}) => {
  return (
    <>
      {structure.map((item: PropertyWithIdentifier) => (
        <Property property={item} key={item.id} onClick={onChange} />
      ))}
    </>
  );
};

export {PropertiesList};
