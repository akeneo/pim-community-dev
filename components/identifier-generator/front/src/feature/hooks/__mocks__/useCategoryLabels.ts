import {CategoryCode} from '@akeneo-pim-community/shared';

type CategoryLabels = {[categoryCode: string]: string | null};

const useCategoryLabels: (categoryCodes: CategoryCode[]) => CategoryLabels = () => {
  return {
    category1: 'Category 1',
  };
};

export {useCategoryLabels};
