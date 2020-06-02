import React from 'react';
import { RemoveActionLine } from '../../pages/EditRules/components/actions/RemoveActionLine';
import { ActionLineProps } from '../../pages/EditRules/components/actions/ActionLineProps';
import { ProductField } from './ProductField';

export type RemoveAction = {
  module: React.FC<{ action: RemoveAction } & ActionLineProps>;
  type: 'remove';
  items: string[];
  include_children: boolean | null;
} & ProductField;

export const denormalizeRemoveAction = async (
  json: any
): Promise<RemoveAction | null> => {
  if (json.type !== 'remove') {
    return null;
  }

  return Promise.resolve({
    module: RemoveActionLine,
    type: 'remove',
    items: json.items,
    field: json.field,
    locale: json.locale || null,
    scope: json.scope || null,
    include_children: json.include_children || null,
  });
};
