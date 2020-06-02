import React from 'react';
import { ClearActionLine } from '../../pages/EditRules/components/actions/ClearActionLine';
import { ActionLineProps } from '../../pages/EditRules/components/actions/ActionLineProps';
import { ProductField } from './ProductField';

export type ClearAction = {
  module: React.FC<{ action: ClearAction } & ActionLineProps>;
  type: 'clear';
} & ProductField;

export const denormalizeClearAction = async (
  json: any
): Promise<ClearAction | null> => {
  if (json.type !== 'clear') {
    return null;
  }

  return Promise.resolve({
    module: ClearActionLine,
    type: 'clear',
    field: json.field,
    locale: json.locale || null,
    scope: json.scope || null,
  });
};
