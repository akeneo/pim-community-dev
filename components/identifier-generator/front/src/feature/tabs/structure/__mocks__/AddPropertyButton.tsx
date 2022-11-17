import React from 'react';
import {Property, PROPERTY_NAMES} from '../../../models';

type AddPropertyButtonProps = {
  onAddProperty: (property: Property) => void;
};

const AddPropertyButton: React.FC<AddPropertyButtonProps> = ({onAddProperty}) => {
  const addProperty = () => {
    onAddProperty({
      type: PROPERTY_NAMES.FREE_TEXT,
      string: 'New property',
    });
  };
  return (
    <>
      AddPropertyButtonMock
      <button onClick={addProperty}>Add Property</button>
    </>
  );
};

export {AddPropertyButton};
