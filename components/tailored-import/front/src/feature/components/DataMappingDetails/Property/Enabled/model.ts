import {Target, TargetNotEmptyAction, TargetEmptyAction} from '../../../../models';

const ENABLED_PROPERTY_CODE = 'enabled';

type EnabledTarget = {
  code: typeof ENABLED_PROPERTY_CODE;
  type: 'property';
  action_if_not_empty: TargetNotEmptyAction;
  action_if_empty: TargetEmptyAction;
};

const isEnabledTarget = (target: Target): target is EnabledTarget =>
  'property' === target.type && ENABLED_PROPERTY_CODE === target.code;

export type {EnabledTarget};
export {isEnabledTarget};
