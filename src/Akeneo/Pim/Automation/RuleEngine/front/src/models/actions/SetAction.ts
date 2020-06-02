import React from 'react';
import { SetActionLine } from '../../pages/EditRules/components/actions/SetActionLine';
import { ActionLineProps } from '../../pages/EditRules/components/actions/ActionLineProps';
import { ProductField } from './ProductField';

export type SetAction = {
  module: React.FC<{ action: SetAction } & ActionLineProps>;
  type: 'set';
  value: any;
} & ProductField;

export const denormalizeSetAction = async (
  json: any
): Promise<SetAction | null> => {
  if (json.type !== 'set') {
    return null;
  }

  return Promise.resolve({
    module: SetActionLine,
    type: 'set',
    field: json.field,
    locale: json.locale || null,
    scope: json.scope || null,
    value: json.value,
  });
};
