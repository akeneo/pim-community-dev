import React from 'react';
import { ConcatenateActionLine } from '../../pages/EditRules/components/actions/ConcatenateActionLine';
import { ActionLineProps } from '../../pages/EditRules/components/actions/ActionLineProps';
import { ProductField } from './ProductField';

export type ConcatenateAction = {
  module: React.FC<{ action: ConcatenateAction } & ActionLineProps>;
  type: 'concatenate';
  from: ProductField[];
  to: ProductField;
};

export const denormalizeConcatenateAction = async (
  json: any
): Promise<ConcatenateAction | null> => {
  if (json.type !== 'concatenate') {
    return null;
  }

  return Promise.resolve({
    module: ConcatenateActionLine,
    type: 'concatenate',
    from: json.from,
    to: json.to,
  });
};
