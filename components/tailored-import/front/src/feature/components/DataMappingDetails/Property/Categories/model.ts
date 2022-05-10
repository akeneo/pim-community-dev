import {Target, TargetNotEmptyAction, TargetEmptyAction} from '../../../../models';

const CATEGORIES_PROPERTY_CODE = 'categories';

type CategoryTarget = {
  code: typeof CATEGORIES_PROPERTY_CODE;
  type: 'property';
  action_if_not_empty: TargetNotEmptyAction;
  action_if_empty: TargetEmptyAction;
};

const getDefaultCategoryTarget = (): CategoryTarget => ({
  code: CATEGORIES_PROPERTY_CODE,
  type: 'property',
  action_if_not_empty: 'set',
  action_if_empty: 'skip',
});

const isCategoryTarget = (target: Target): target is CategoryTarget =>
  'property' === target.type && 'categories' === target.code;

export type {CategoryTarget};
export {getDefaultCategoryTarget, isCategoryTarget};
