import React from 'react';
import {ColumnCode, ColumnDefinition} from '../../models';

type AddColumnModalProps = {
  close: () => void;
  onCreate: (columnDefinition: ColumnDefinition) => void;
  existingColumnCodes: ColumnCode[];
};

const AddColumnModal: React.FC<AddColumnModalProps> = ({close, onCreate}) => {
  const handleCreate = () => {
    close();
    onCreate({
      code: 'new_column',
      labels: {en_US: 'New column'},
      data_type: 'text',
      validations: {},
    });
  };

  return <button onClick={handleCreate}>Mock create</button>;
};

export {AddColumnModal};
