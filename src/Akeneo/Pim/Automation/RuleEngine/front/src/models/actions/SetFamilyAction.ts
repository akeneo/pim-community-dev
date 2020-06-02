import React from 'react';
import { ActionLineProps } from '../../pages/EditRules/components/actions/ActionLineProps';
import { SetFamilyActionLine } from '../../pages/EditRules/components/actions/SetFamilyActionLine';
import { FamilyCode } from '../Family';

export type SetFamilyAction = {
  module: React.FC<{ action: SetFamilyAction } & ActionLineProps>;
  type: 'set';
  field: 'family';
  value: FamilyCode | null;
};

export const denormalizeSetFamilyAction = (
  json: any
): SetFamilyAction | null => {
  if (json.type !== 'set') {
    return null;
  }
  if (json.field !== 'family') {
    return null;
  }

  return {
    module: SetFamilyActionLine,
    type: 'set',
    field: 'family',
    value: json.value || null,
  };
};

export const createSetFamilyAction: () => SetFamilyAction = () => {
  return {
    module: SetFamilyActionLine,
    type: 'set',
    field: 'family',
    value: null,
  };
};
