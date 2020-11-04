import {Category, CategoryCode} from '../../../src/models';

export const createCategory = (code: CategoryCode, data?: any): Category => {
  return {
    code,
    parent: `parent_${code}`,
    labels: {en_US: `Label en_US for ${code}`},
    id: Math.round(Math.random() * 10000),
    root: Math.round(Math.random() * 10000),
    ...data,
  };
};
