import React from 'react';
import {BooleanReplacementOperation} from '../BooleanReplacement';

const {isBooleanReplacementOperation} = jest.requireActual('../BooleanReplacement');

const BooleanReplacement = ({
  onOperationChange,
}: {
  onOperationChange: (updatedOperation: BooleanReplacementOperation) => void;
}) => (
  <button
    onClick={() =>
      onOperationChange({
        type: 'replacement',
        mapping: {
          true: 'activé',
          false: 'désactivé',
        },
      })
    }
  >
    Update replacement
  </button>
);

export {isBooleanReplacementOperation, BooleanReplacement};
