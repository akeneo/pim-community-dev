import React, {FunctionComponent} from "react";
import {DATA_QUALITY_INSIGHTS_DASHBOARD_CHANGE_PERIODICITY} from "../../../../../infrastructure/context-provider";

const __ = require('oro/translator');

const handlePeriodicityChange = (value: string) => {
  window.dispatchEvent(new CustomEvent(DATA_QUALITY_INSIGHTS_DASHBOARD_CHANGE_PERIODICITY, {detail: {
      periodicity: value
    }}));
};

interface PeriodicityFilterProps {
  periodicity: string;
}

const PeriodicityFilter: FunctionComponent<PeriodicityFilterProps> = ({periodicity}) => {

  return (
    <div className="AknFilterBox-filterContainer AknDropdown">
      <button type="button" className="AknFilterBox-filter AknActionButton AknActionButton--withoutBorder" data-toggle="dropdown">
        <span className="AknFilterBox-filterLabel">{__('akeneo_data_quality_insights.dqi_dashboard.periodicity.label')}</span>
        <span className="AknActionButton-highlight">
          <span>{__(`akeneo_data_quality_insights.dqi_dashboard.periodicity.${periodicity}`)}</span>
          <span className="AknActionButton-caret"/>
        </span>
      </button>
      <ul className="AknDropdown-menu">
        <div className="AknDropdown-menuTitle">{__('akeneo_data_quality_insights.dqi_dashboard.periodicity.daily')}</div>
        <li>
          <a
            className={`AknDropdown-menuLink ${periodicity === 'daily' ? 'AknDropdown-menuLink--active' : ''}`}
            data-label={__('akeneo_data_quality_insights.dqi_dashboard.periodicity.daily')}
            onClick={() => handlePeriodicityChange('daily')}
          >
            {__('akeneo_data_quality_insights.dqi_dashboard.periodicity.daily')}
          </a>
        </li>
        <li>
          <a
            className={`AknDropdown-menuLink ${periodicity === 'weekly' ? 'AknDropdown-menuLink--active' : ''}`}
            data-label={__('akeneo_data_quality_insights.dqi_dashboard.periodicity.weekly')}
            onClick={() => handlePeriodicityChange('weekly')}
          >
            {__('akeneo_data_quality_insights.dqi_dashboard.periodicity.weekly')}
          </a>
        </li>
        <li>
          <a
            className={`AknDropdown-menuLink ${periodicity === 'monthly' ? 'AknDropdown-menuLink--active' : ''}`}
            data-label={__('akeneo_data_quality_insights.dqi_dashboard.periodicity.monthly')}
            onClick={() => handlePeriodicityChange('monthly')}
          >
            {__('akeneo_data_quality_insights.dqi_dashboard.periodicity.monthly')}
          </a>
        </li>
      </ul>
    </div>
  )

};

export default PeriodicityFilter;
