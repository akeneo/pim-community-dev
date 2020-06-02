import React from 'react';
import { ClearActionLine } from '../../pages/EditRules/components/actions/ClearActionLine';
import { ActionLineProps } from '../../pages/EditRules/components/actions/ActionLineProps';
import { ProductField } from './ProductField';

export type ClearAction = {
  module: React.FC<{ action: ClearAction } & ActionLineProps>;
  type: 'clear';
} & ProductField;

export const denormalizeClearAction = (json: any): ClearAction | null => {
  if (json.type !== 'clear') {
    return null;
  }

  return {
    module: ClearActionLine,
    type: 'clear',
    field: json.field,
    locale: json.locale || null,
    scope: json.scope || null,
  };
};

export const createClearAction: () => ClearAction = () => {
  return {
    module: ClearActionLine,
    type: 'clear',
    field: '',
    locale: null,
    scope: null,
  };
};
