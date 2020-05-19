import React from 'react';
import { ConcatenateActionLine } from '../../pages/EditRules/components/actions/ConcatenateActionLine';
import { ActionLineProps } from '../../pages/EditRules/components/actions/ActionLineProps';
import { ProductField } from './ProductField';

export type ConcatenateAction = {
  module: React.FC<{ action: ConcatenateAction } & ActionLineProps>;
  from: ProductField[];
  to: ProductField;
};

const addLocaleAndScope = (data: any) => {
  return {
    scope: null,
    locale: null,
    ...data,
  };
};

export const denormalizeConcatenateAction = (
  json: any
): ConcatenateAction | null => {
  if (json.type !== 'concatenate') {
    return null;
  }

  return {
    module: ConcatenateActionLine,
    from: json.from.map((item: any) => addLocaleAndScope(item)),
    to: addLocaleAndScope(json.to),
  };
};
