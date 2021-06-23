import React from 'react';
import {ColumnCode, ColumnDefinition} from '../../models/TableConfiguration';
import {LabelCollection} from '@akeneo-pim-community/shared';

type AddColumnModalProps = {
  close: () => void;
  onCreate: (columnDefinition: ColumnDefinition) => void;
  existingColumnCodes: ColumnCode[];
};

const AddColumnModal: React.FC<AddColumnModalProps> = ({close, onCreate}) => {
  const handleCreate = () => {
    const labels: LabelCollection = {};
    labels['en_US'] = 'New column';
    close();
    onCreate({
      code: 'new_column',
      labels: labels,
      data_type: 'text',
      validations: {},
    });
  };

  return <button onClick={handleCreate}>Mock create</button>;
};

export {AddColumnModal};
