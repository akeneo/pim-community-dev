import {LabelCollection} from "@akeneo-pim-community/shared";

type CategoryTree = {
  id: number,
  code: string,
  labels: LabelCollection,
};

type Category = {
  id: number,
  code: string,
  label: string,
  isLeaf: boolean,
}

export type {CategoryTree, Category};
