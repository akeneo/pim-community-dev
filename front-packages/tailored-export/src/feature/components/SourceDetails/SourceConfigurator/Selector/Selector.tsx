import React from 'react';
import {Selection, Attribute} from '../../../../models';
import {CodeLabelSelector} from './CodeLabelSelector';
import {MeasurementSelector} from './MeasurementSelector';

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
    case 'akeneo_reference_entity_collection':
    case 'pim_catalog_asset_collection':
      return <CodeLabelSelector selection={selection} onSelectionChange={onSelectionChange} />;
    case 'pim_catalog_metric':
      return <MeasurementSelector selection={selection} onSelectionChange={onSelectionChange} />;
    default:
      return null;
  }
};

export {Selector};
