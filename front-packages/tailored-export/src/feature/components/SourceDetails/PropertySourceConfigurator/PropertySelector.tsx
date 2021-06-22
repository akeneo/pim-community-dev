import React from 'react';
import {ValidationError} from '@akeneo-pim-community/shared';
import {Selection, CodeLabelCollectionSelection, CodeLabelSelection} from '../../../models';
import {CodeLabelSelector} from '../Selector/CodeLabelSelector';
import {CodeLabelCollectionSelector} from '../Selector/CodeLabelCollectionSelector';

type PropertySelectorProps = {
  propertyName: string;
  selection: Selection;
  validationErrors: ValidationError[];
  onSelectionChange: (selection: Selection) => void;
};

const PropertySelector = ({propertyName, selection, validationErrors, onSelectionChange}: PropertySelectorProps) => {
  switch (propertyName) {
    case 'family':
      return (
        <CodeLabelSelector
          selection={selection as CodeLabelSelection}
          validationErrors={validationErrors}
          onSelectionChange={onSelectionChange}
        />
      );
    case 'categories':
      return (
        <CodeLabelCollectionSelector
          selection={selection as CodeLabelCollectionSelection}
          validationErrors={validationErrors}
          onSelectionChange={onSelectionChange}
        />
      );
    default:
      return null;
  }
};

export {PropertySelector};
