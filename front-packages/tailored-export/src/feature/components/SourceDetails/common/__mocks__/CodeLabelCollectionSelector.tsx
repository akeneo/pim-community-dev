import React from 'react';
import {CodeLabelCollectionSelection} from '../CodeLabelCollectionSelector';

const {isCodeLabelCollectionSelection} = jest.requireActual('../CodeLabelCollectionSelector');

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

export {isCodeLabelCollectionSelection, CodeLabelCollectionSelector};
