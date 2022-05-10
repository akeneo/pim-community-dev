import {Target, TargetNotEmptyAction, TargetEmptyAction} from '../../../../models';

const CATEGORIES_PROPERTY_CODE = 'categories';

type CategoriesTarget = {
  code: typeof CATEGORIES_PROPERTY_CODE;
  type: 'property';
  action_if_not_empty: TargetNotEmptyAction;
  action_if_empty: TargetEmptyAction;
};

const isCategoriesTarget = (target: Target): target is CategoriesTarget =>
  'property' === target.type && 'categories' === target.code;

export type {CategoriesTarget};
export {isCategoriesTarget};
