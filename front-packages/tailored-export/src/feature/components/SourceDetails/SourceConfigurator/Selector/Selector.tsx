import React from 'react';
import {Selection, Attribute} from '../../../../models';
import {CodeLabelSelector} from './CodeLabelSelector';

type SelectorProps = {
  attribute: Attribute;
  selection: Selection;
  onSelectionChange: (selection: Selection) => void;
};

const Selector = ({attribute, selection, onSelectionChange}: SelectorProps) => {
  switch (attribute.type) {
    case 'pim_catalog_simpleselect':
    case 'pim_catalog_multiselect':
    case 'akeneo_reference_entity':
      return <CodeLabelSelector selection={selection} onSelectionChange={onSelectionChange} />;
    default:
      return null;
  }
};

export {Selector};
