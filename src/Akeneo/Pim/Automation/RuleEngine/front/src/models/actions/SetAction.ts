import React from 'react';
import { SetActionLine } from '../../pages/EditRules/components/actions/SetActionLine';
import { ActionLineProps } from '../../pages/EditRules/components/actions/ActionLineProps';
import { ProductField } from './ProductField';

export type SetAction = {
  module: React.FC<{ action: SetAction } & ActionLineProps>;
  type: 'set';
  value: any;
} & ProductField;

export const denormalizeSetAction = (json: any): SetAction | null => {
  if (json.type !== 'set') {
    return null;
  }

  return {
    module: SetActionLine,
    type: 'set',
    field: json.field,
    locale: json.locale || null,
    scope: json.scope || null,
    value: json.value,
  };
};
