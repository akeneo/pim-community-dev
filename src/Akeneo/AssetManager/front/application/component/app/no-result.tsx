import __ from 'akeneoassetmanager/tools/translator';
import * as React from 'react';

const NoResult = ({
  entityLabel,
  title = 'pim_asset_manager.asset.no_result.title',
  subtitle = 'pim_asset_manager.asset.no_result.subtitle',
  type = 'asset-family',
}: {
  entityLabel: string;
  title?: string;
  subtitle?: string;
  type?: string;
}) => {
  return (
    <div className="AknGridContainer-noData">
      <div className={`AknGridContainer-noDataImage AknGridContainer-noDataImage--${type}`} />
      <div className="AknGridContainer-noDataTitle">
        {__(title, {
          entityLabel,
        })}
      </div>
      <div className="AknGridContainer-noDataSubtitle">{__(subtitle)}</div>
    </div>
  );
};

export default NoResult;
