import React, {FunctionComponent} from 'react';
import FamilyFilter from "./Filters/FamilyFilter";
import TimePeriodFilter from "./Filters/TimePeriodFilter";
import CategoryFilter from "./Filters/CategoryFilter";

const __ = require('oro/translator');
const SecurityContext = require('pim/security-context');

interface DataQualityOverviewHeaderProps {
  timePeriod: string;
  familyCode: string | null;
  categoryCode: string | null;
}

const Filters: FunctionComponent<DataQualityOverviewHeaderProps> = ({timePeriod, familyCode, categoryCode}) => {

  return (
    <div className="AknSubsection-title AknSubsection-title--glued">
      <span>{__('akeneo_data_quality_insights.dqi_dashboard.title')}</span>
      <div className="AknFilterBox AknFilterBox--search">
        <div className="AknFilterBox-list filter-box">

          <TimePeriodFilter timePeriod={timePeriod}/>

          {SecurityContext.isGranted('pim_enrich_product_category_list') && (<CategoryFilter categoryCode={categoryCode}/>)}

          <FamilyFilter familyCode={familyCode} />

        </div>
      </div>
    </div>
  );
};

export default Filters;
