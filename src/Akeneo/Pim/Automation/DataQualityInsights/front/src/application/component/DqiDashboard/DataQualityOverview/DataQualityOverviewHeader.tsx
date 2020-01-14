import React, {FunctionComponent} from 'react';
import {DATA_QUALITY_INSIGHTS_DASHBOARD_CHANGE_PERIODICITY} from "../../../../infrastructure/context-provider";

const __ = require('oro/translator');

interface DataQualityOverviewHeaderProps {
  periodicity: string;
}

const handlePeriodicityChange = (value: string) => {
  window.dispatchEvent(new CustomEvent(DATA_QUALITY_INSIGHTS_DASHBOARD_CHANGE_PERIODICITY, {detail: {
    periodicity: value
  }}));
};

const DataQualityOverviewHeader: FunctionComponent<DataQualityOverviewHeaderProps> = ({periodicity}) => {

  return (
    <div className="AknSubsection-title AknSubsection-title--glued">
      <span>{__('akeneo_data_quality_insights.dqi_dashboard.title')}</span>
      <div className="AknFilterBox AknFilterBox--search">
        <div className="AknFilterBox-list filter-box">
          <div className="AknFilterBox-filterContainer AknDropdown">
            <button type="button" className="AknFilterBox-filter AknActionButton AknActionButton--withoutBorder" data-toggle="dropdown">
              <span className="AknFilterBox-filterLabel">{__('akeneo_data_quality_insights.dqi_dashboard.periodicity.label')}</span>
              <span className="AknActionButton-highlight">
                <span>{__(`akeneo_data_quality_insights.dqi_dashboard.periodicity.${periodicity}`)}</span>
                <span className="AknActionButton-caret"></span>
              </span>
            </button>
            <ul className="AknDropdown-menu">
              <div className="AknDropdown-menuTitle">{__('akeneo_data_quality_insights.dqi_dashboard.periodicity.daily')}</div>
              <li>
                <a className="AknDropdown-menuLink" data-label={__('akeneo_data_quality_insights.dqi_dashboard.periodicity.daily')} onClick={() => handlePeriodicityChange('daily')}>
                  {__('akeneo_data_quality_insights.dqi_dashboard.periodicity.daily')}
                </a>
              </li>
              <li>
                <a className="AknDropdown-menuLink" data-label={__('akeneo_data_quality_insights.dqi_dashboard.periodicity.weekly')} onClick={() => handlePeriodicityChange('weekly')}>
                  {__('akeneo_data_quality_insights.dqi_dashboard.periodicity.weekly')}
                </a>
              </li>
              <li>
                <a className="AknDropdown-menuLink" data-label={__('akeneo_data_quality_insights.dqi_dashboard.periodicity.monthly')} onClick={() => handlePeriodicityChange('monthly')}>
                  {__('akeneo_data_quality_insights.dqi_dashboard.periodicity.monthly')}
                </a>
              </li>
            </ul>
          </div>
          <div className="AknFilterBox-filterContainer">
            <div className="AknFilterBox-filter">
              <span className="AknFilterBox-filterLabel">{__('akeneo_data_quality_insights.dqi_dashboard.category.label')}</span>
              <button type="button" className="AknFilterBox-filterCriteria ui-multiselect">
                <span>{__('pim_common.all')}</span>
                <span className="AknFilterBox-filterCaret"></span>
              </button>
            </div>
          </div>
          <div className="AknFilterBox-filterContainer">
            <div className="AknFilterBox-filter">
              <span className="AknFilterBox-filterLabel">{__('akeneo_data_quality_insights.dqi_dashboard.family.label')}</span>
              <button type="button" className="AknFilterBox-filterCriteria ui-multiselect">
                <span>{__('pim_common.all')}</span>
                <span className="AknFilterBox-filterCaret"></span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default DataQualityOverviewHeader;
