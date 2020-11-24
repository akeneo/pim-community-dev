import React, {FC} from 'react';
import {CategoryFilter, FamilyFilter, TimePeriodFilter} from './Filters';
import {useSecurity, useTranslate} from '@akeneo-pim-community/legacy-bridge';

type Props = {
  timePeriod: string;
  familyCode: string | null;
  categoryCode: string | null;
};

const Header: FC<Props> = ({timePeriod, familyCode, categoryCode}) => {
  const translate = useTranslate();
  const {isGranted} = useSecurity();
  return (
    <div className="AknSubsection-title AknSubsection-title--glued">
      <span>{translate('akeneo_data_quality_insights.dqi_dashboard.score_distribution.title')}</span>
      <div className="AknFilterBox AknFilterBox--search">
        <div className="AknFilterBox-list filter-box">
          <TimePeriodFilter timePeriod={timePeriod} />

          {isGranted('pim_enrich_product_category_list') && <CategoryFilter categoryCode={categoryCode} />}

          <FamilyFilter familyCode={familyCode} />
        </div>
      </div>
    </div>
  );
};

export {Header};
