import React, {FunctionComponent} from 'react';

interface DataQualityOverviewHeaderProps {

}

const DataQualityOverviewHeader: FunctionComponent<DataQualityOverviewHeaderProps> = () => {

  return (
    <div>
      <div className="AknSubsection-title AknSubsection-title--glued">
        <span>Data Quality Overview</span>
        <div className="AknFilterBox AknFilterBox--search">
          <div className="AknFilterBox-list filter-box">
            <div className="AknFilterBox-filterContainer">
              <div className="AknFilterBox-filter">
                <span className="AknFilterBox-filterLabel">Time lapse</span>
                <button type="button" className="AknFilterBox-filterCriteria ui-multiselect">
                  <span>Daily</span>
                  <span className="AknFilterBox-filterCaret"></span>
                </button>
              </div>
            </div>
            <div className="AknFilterBox-filterContainer">
              <div className="AknFilterBox-filter">
                <span className="AknFilterBox-filterLabel">Category</span>
                <button type="button" className="AknFilterBox-filterCriteria ui-multiselect">
                  <span>All</span>
                  <span className="AknFilterBox-filterCaret"></span>
                </button>
              </div>
            </div>
            <div className="AknFilterBox-filterContainer">
              <div className="AknFilterBox-filter">
                <span className="AknFilterBox-filterLabel">Family</span>
                <button type="button" className="AknFilterBox-filterCriteria ui-multiselect">
                  <span>All</span>
                  <span className="AknFilterBox-filterCaret"></span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default DataQualityOverviewHeader;
