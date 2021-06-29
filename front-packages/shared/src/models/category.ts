import {LabelCollection} from './label-collection';

type CategoryCode = string;

type Category = {
  id: number;
  code: CategoryCode;
  parent: CategoryCode | null;
  labels: LabelCollection;
};

export type {Category, CategoryCode};
