import React from 'react';
import {CodeLabelSelection} from '../CodeLabelSelector';

const {isCodeLabelSelection, getDefaultCodeLabelSelection, isDefaultCodeLabelSelection} =
  jest.requireActual('../CodeLabelSelector');

const CodeLabelSelector = ({
  onSelectionChange,
}: {
  onSelectionChange: (updatedSelection: CodeLabelSelection) => void;
}) => (
  <button
    onClick={() =>
      onSelectionChange({
        type: 'label',
        locale: 'en_US',
      })
    }
  >
    Update selection
  </button>
);

export {isCodeLabelSelection, getDefaultCodeLabelSelection, isDefaultCodeLabelSelection, CodeLabelSelector};
