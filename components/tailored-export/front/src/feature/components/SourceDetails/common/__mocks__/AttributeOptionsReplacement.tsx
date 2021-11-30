import React from 'react';
import {ReplacementOperation} from '../ReplacementOperation';

const AttributeOptionsReplacement = ({
  onOperationChange,
}: {
  onOperationChange: (updatedOperation: ReplacementOperation) => void;
}) => (
  <button
    onClick={() =>
      onOperationChange({
        type: 'replacement',
        mapping: {
          blue: 'Bleu',
        },
      })
    }
  >
    Attribute options replacement
  </button>
);

export {AttributeOptionsReplacement};
