import React, {FC} from 'react';
import {
  CategoryFilter,
  FamilyFilter,
} from '@akeneo-pim-community/data-quality-insights/src/application/component/Dashboard/Filters';
import {DashboardContextProvider} from '@akeneo-pim-community/data-quality-insights/src/application/context/DashboardContext';
import {useSecurity} from '@akeneo-pim-community/legacy-bridge';

const BaseDashboard = require('akeneo/data-quality-insights/view/dqi-dashboard/base-dashboard');

type Props = {
  categoryCode: string | null;
  categoryId: string | null;
  rootCategoryId: string | null;
  familyCode: string | null;
};

const Wrapper: FC<Props> = ({categoryCode, categoryId, rootCategoryId, familyCode}) => {
  const {isGranted} = useSecurity();
  const category =
    categoryCode === null || categoryId === null || rootCategoryId === null
      ? null
      : {
          id: categoryId,
          code: categoryCode,
          rootCategoryId,
        };

  return (
    <DashboardContextProvider familyCode={familyCode} category={category}>
      {isGranted('pim_enrich_product_category_list') && <CategoryFilter categoryCode={categoryCode} />}
      <FamilyFilter familyCode={familyCode} />
    </DashboardContextProvider>
  );
};

class DashboardFilters extends BaseDashboard {
  constructor(options: any) {
    super({...options, ...{className: 'AknButtonList'}});
  }

  render() {
    this.renderReact(
      Wrapper,
      {
        familyCode: this.familyCode,
        categoryCode: this.categoryCode,
        categoryId: this.categoryId,
        rootCategoryId: this.rootCategoryId,
      },
      this.el
    );

    return this;
  }
}

export = DashboardFilters;
