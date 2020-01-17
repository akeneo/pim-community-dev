import React, {FunctionComponent} from 'react';
import FamilyFilter from "./Header/FamilyFilter";
import PeriodicityFilter from "./Header/PeriodicityFilter";

const __ = require('oro/translator');

interface DataQualityOverviewHeaderProps {
  periodicity: string;
  familyCode: string | null;
}

const DataQualityOverviewHeader: FunctionComponent<DataQualityOverviewHeaderProps> = ({periodicity, familyCode}) => {

  return (
    <div className="AknSubsection-title AknSubsection-title--glued">
      <span>{__('akeneo_data_quality_insights.dqi_dashboard.title')}</span>
      <div className="AknFilterBox AknFilterBox--search">
        <div className="AknFilterBox-list filter-box">

          <PeriodicityFilter periodicity={periodicity}/>

          <div className="AknFilterBox-filterContainer">
            <div className="AknFilterBox-filter">
              <span className="AknFilterBox-filterLabel">{__('akeneo_data_quality_insights.dqi_dashboard.category.label')}</span>
              <button type="button" className="AknFilterBox-filterCriteria ui-multiselect">
                <span>{__('pim_common.all')}</span>
              </button>
            </div>
          </div>

          <FamilyFilter familyCode={familyCode} />

        </div>
      </div>
    </div>
  );
};

export default DataQualityOverviewHeader;
