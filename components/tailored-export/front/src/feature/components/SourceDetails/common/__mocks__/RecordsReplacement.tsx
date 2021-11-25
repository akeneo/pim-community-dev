import React from 'react';
import {ReplacementOperation} from '../ReplacementOperation';

type RecordsReplacementProps = {
  onOperationChange: (updatedOperation: ReplacementOperation) => void;
};

const RecordsReplacement = ({onOperationChange}: RecordsReplacementProps) => (
  <button
    onClick={() =>
      onOperationChange({
        type: 'replacement',
        mapping: {
          foo: 'bar',
        },
      })
    }
  >
    Records replacement
  </button>
);

export {RecordsReplacement};
