import React, {FunctionComponent} from 'react';
import FamilyFilter from "./Header/FamilyFilter";
import PeriodicityFilter from "./Header/PeriodicityFilter";
import CategoryFilter from "./Header/CategoryFilter";

const __ = require('oro/translator');

interface DataQualityOverviewHeaderProps {
  periodicity: string;
  familyCode: string | null;
  categoryCode: string | null;
}

const DataQualityOverviewHeader: FunctionComponent<DataQualityOverviewHeaderProps> = ({periodicity, familyCode, categoryCode}) => {

  return (
    <div className="AknSubsection-title AknSubsection-title--glued">
      <span>{__('akeneo_data_quality_insights.dqi_dashboard.title')}</span>
      <div className="AknFilterBox AknFilterBox--search">
        <div className="AknFilterBox-list filter-box">

          <PeriodicityFilter periodicity={periodicity}/>

          <CategoryFilter categoryCode={categoryCode}/>

          <FamilyFilter familyCode={familyCode} />

        </div>
      </div>
    </div>
  );
};

export default DataQualityOverviewHeader;
