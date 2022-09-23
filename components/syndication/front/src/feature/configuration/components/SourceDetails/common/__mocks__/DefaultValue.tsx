import React from 'react';
import {DefaultValueOperation} from '../DefaultValue';

const DefaultValue = ({onOperationChange}: {onOperationChange: (updatedOperation: DefaultValueOperation) => void}) => (
  <button
    onClick={() =>
      onOperationChange({
        type: 'default_value',
        value: 'foo',
      })
    }
  >
    Default value
  </button>
);

export {DefaultValue};
