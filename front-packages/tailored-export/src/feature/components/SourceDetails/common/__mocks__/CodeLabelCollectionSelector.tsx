import React from 'react';
import {CodeLabelCollectionSelection} from '../CodeLabelCollectionSelector';

const {isCodeLabelCollectionSelection, getDefaultCodeLabelCollectionSelection, isDefaultCodeLabelCollectionSelection} =
  jest.requireActual('../CodeLabelCollectionSelector');

const CodeLabelCollectionSelector = ({
  onSelectionChange,
}: {
  onSelectionChange: (updatedSelection: CodeLabelCollectionSelection) => void;
}) => (
  <button
    onClick={() =>
      onSelectionChange({
        type: 'label',
        locale: 'en_US',
        separator: ',',
      })
    }
  >
    Update selection
  </button>
);

export {
  CodeLabelCollectionSelector,
  getDefaultCodeLabelCollectionSelection,
  isCodeLabelCollectionSelection,
  isDefaultCodeLabelCollectionSelection,
};
