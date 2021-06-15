import React from 'react';
import {
  Selection,
  Attribute,
  CodeLabelCollectionSelection,
  CodeLabelSelection,
  MeasurementSelection, PriceCollectionSelection
} from '../../../../models';
import {CodeLabelSelector} from './CodeLabelSelector';
import {MeasurementSelector} from './MeasurementSelector';
import {PriceCollectionSelector} from './PriceCollectionSelector';
import {CodeLabelCollectionSelector} from "./CodeLabelCollectionSelector";

type SelectorProps = {
  attribute: Attribute;
  selection: Selection;
  onSelectionChange: (selection: Selection) => void;
};

const Selector = ({attribute, selection, onSelectionChange}: SelectorProps) => {
  switch (attribute.type) {
    case 'akeneo_reference_entity_collection':
    case 'pim_catalog_asset_collection':
    case 'pim_catalog_multiselect':
      return <CodeLabelCollectionSelector selection={selection as CodeLabelCollectionSelection} onSelectionChange={onSelectionChange} />;
    case 'akeneo_reference_entity':
    case 'pim_catalog_simpleselect':
      return <CodeLabelSelector selection={selection as CodeLabelSelection} onSelectionChange={onSelectionChange} />;
    case 'pim_catalog_metric':
      return <MeasurementSelector selection={selection as MeasurementSelection} onSelectionChange={onSelectionChange} />;
    case 'pim_catalog_price_collection':
      return <PriceCollectionSelector selection={selection as PriceCollectionSelection} onSelectionChange={onSelectionChange} />;
    default:
      return null;
  }
};

export {Selector};
