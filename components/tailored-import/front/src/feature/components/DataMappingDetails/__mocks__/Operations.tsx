import React from 'react';
import {OperationsProps} from '../Operations';

const Operations = ({onRefreshSampleData}: OperationsProps) => {
  return (
    <>
      <h1>Operations</h1>
      <button onClick={() => onRefreshSampleData(1)}>Refresh data</button>
    </>
  );
};

export {Operations};
