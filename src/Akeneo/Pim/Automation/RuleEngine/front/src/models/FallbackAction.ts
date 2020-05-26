import React from 'react';
import { FallbackActionLine } from '../pages/EditRules/components/actions/FallbackActionLine';
import { ActionLineProps } from '../pages/EditRules/components/actions/ActionLineProps';

export type FallbackAction = {
  module: React.FC<{ action: FallbackAction } & ActionLineProps>;
  json: any;
};

export const denormalizeFallbackAction = (json: any): FallbackAction => {
  return {
    module: FallbackActionLine,
    json,
  };
};
