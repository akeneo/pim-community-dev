import {Target, TargetNotEmptyAction, TargetEmptyAction} from '../../../../models';

const FAMILY_PROPERTY_CODE = 'family';

type FamilyTarget = {
  code: typeof FAMILY_PROPERTY_CODE;
  type: 'property';
  action_if_not_empty: TargetNotEmptyAction;
  action_if_empty: TargetEmptyAction;
};

const isFamilyTarget = (target: Target): target is FamilyTarget =>
  'property' === target.type && FAMILY_PROPERTY_CODE === target.code;

export type {FamilyTarget};
export {isFamilyTarget};
