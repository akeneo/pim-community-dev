import React from 'react';

const __ = require('oro/translator');

const DashboardHelper = React.memo(() => {

  let helper = <></>;
  const showHelper = localStorage.getItem('data-quality-insights:dashboard:show-helper');

  if (showHelper === null) {
    localStorage.setItem('data-quality-insights:dashboard:show-helper', '0');
    helper =
      <div className="AknDescriptionHeader">
        <div className="AknDescriptionHeader-icon" style={{backgroundImage: `url("bundles/akeneopimenterprisedataqualityinsights/images/AddingValues.svg")`}}/>
        <div className="AknDescriptionHeader-title">
          {__('akeneo_data_quality_insights.dqi_dashboard.helper.title')}
          <div className="AknDescriptionHeader-description">
            <p>
              {__('akeneo_data_quality_insights.dqi_dashboard.helper.description')}
            </p>
            <a href="https://help.akeneo.com/pim/articles/understand-data-quality.html" target="_blank" className="AknDescriptionHeader-link">
              {__('akeneo_data_quality_insights.dqi_dashboard.helper.help_center')}
            </a>
          </div>
        </div>
      </div>;
  }

  return (<>{helper}</>);
});

export default DashboardHelper;
