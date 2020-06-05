import React from 'react';
import { AddToCategoryActionLine } from '../../pages/EditRules/components/actions/AddToCategoryActionLine';
// import { ActionLineProps } from '../../pages/EditRules/components/actions/ActionLineProps';
import { ProductField } from './ProductField';
// import { AddAction } from '.';

export type AddToCategoryActionLine = {
  module: React.FC<any>;
  type: 'add';
  items: string[];
} & ProductField;

export const denormalizeAddToCategoryActionLine = (json: any): any | null => {
  console.log({ json });

  if (json.type !== 'add') {
    return null;
  }

  return {
    module: AddToCategoryActionLine,
    type: 'add',
    items: json.items,
    field: json.field,
    // locale: json.locale || null,
    // scope: json.scope || null,
  };
};
