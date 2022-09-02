import React from 'react';

import {RecordAddRowsButton} from './RecordAddRowsButton';
import {SelectOptionCode} from '../models';
import {SelectAddRowsButton} from './SelectAddRowsButton';
import {useAttributeContext} from '../contexts';

export const TABLE_VALUE_MAX_ROWS = 100;

type AddRowsButtonProps = {
  checkedOptionCodes: SelectOptionCode[];
  toggleChange: (optionCode: SelectOptionCode) => void;
};

const AddRowsButton: React.FC<AddRowsButtonProps> = ({checkedOptionCodes, toggleChange}) => {
  const {attribute} = useAttributeContext();
  const firstColumnDataType = attribute?.table_configuration[0]?.data_type;
  return (
    <>
      {firstColumnDataType === 'reference_entity' ? (
        <RecordAddRowsButton checkedOptionCodes={checkedOptionCodes} toggleChange={toggleChange} />
      ) : (
        <SelectAddRowsButton checkedOptionCodes={checkedOptionCodes} toggleChange={toggleChange} />
      )}
    </>
  );
};

export {AddRowsButton};
