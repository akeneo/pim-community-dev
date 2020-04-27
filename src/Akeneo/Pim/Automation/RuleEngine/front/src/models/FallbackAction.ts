import React from 'react';
import { FallbackActionLine } from '../pages/EditRules/components/FallbackActionLine';
import { ActionLineProps } from '../pages/EditRules/ActionLineProps';

export type FallbackAction = {
  module: React.FC<{ action: FallbackAction } & ActionLineProps>;
  json: any;
};

export const createFallbackAction = (json: any): FallbackAction => {
  return {
    module: FallbackActionLine,
    json,
  };
};
