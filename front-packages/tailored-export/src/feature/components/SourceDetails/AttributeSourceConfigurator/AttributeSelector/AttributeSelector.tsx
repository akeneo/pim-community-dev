import React from 'react';
import {ValidationError} from '@akeneo-pim-community/shared';
import {
  Selection,
  Attribute,
  CodeLabelCollectionSelection,
  CodeLabelSelection,
  MeasurementSelection,
  PriceCollectionSelection,
  DateSelection,
  FileSelection,
} from '../../../../models';
import {CodeLabelSelector} from '../../Selector/CodeLabelSelector';
import {CodeLabelCollectionSelector} from '../../Selector/CodeLabelCollectionSelector';
import {MeasurementSelector} from './MeasurementSelector';
import {PriceCollectionSelector} from './PriceCollectionSelector';
import {DateSelector} from './DateSelector';
import {FileSelector} from './FileSelector';

type AttributeSelectorProps = {
  attribute: Attribute;
  selection: Selection;
  validationErrors: ValidationError[];
  onSelectionChange: (selection: Selection) => void;
};

const AttributeSelector = ({attribute, selection, validationErrors, onSelectionChange}: AttributeSelectorProps) => {
  switch (attribute.type) {
    case 'akeneo_reference_entity_collection':
    case 'pim_catalog_asset_collection':
    case 'pim_catalog_multiselect':
      return (
        <CodeLabelCollectionSelector
          selection={selection as CodeLabelCollectionSelection}
          validationErrors={validationErrors}
          onSelectionChange={onSelectionChange}
        />
      );
    case 'pim_catalog_date':
      return (
        <DateSelector
          selection={selection as DateSelection}
          validationErrors={validationErrors}
          onSelectionChange={onSelectionChange}
        />
      );
    case 'akeneo_reference_entity':
    case 'pim_catalog_simpleselect':
      return (
        <CodeLabelSelector
          selection={selection as CodeLabelSelection}
          validationErrors={validationErrors}
          onSelectionChange={onSelectionChange}
        />
      );
    case 'pim_catalog_metric':
      return (
        <MeasurementSelector
          selection={selection as MeasurementSelection}
          validationErrors={validationErrors}
          onSelectionChange={onSelectionChange}
        />
      );
    case 'pim_catalog_price_collection':
      return (
        <PriceCollectionSelector
          selection={selection as PriceCollectionSelection}
          validationErrors={validationErrors}
          onSelectionChange={onSelectionChange}
        />
      );
    case 'pim_catalog_file':
    case 'pim_catalog_image':
      return (
        <FileSelector
          selection={selection as FileSelection}
          validationErrors={validationErrors}
          onSelectionChange={onSelectionChange}
        />
      );
    default:
      return null;
  }
};

export {AttributeSelector};
