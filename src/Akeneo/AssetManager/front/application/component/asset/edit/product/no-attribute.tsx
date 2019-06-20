import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
const router = require('pim/router');

const NoAttribute = ({
  onRedirectAttributeCreation,
  assetFamilyLabel,
}: {
  onRedirectAttributeCreation: () => void;
  assetFamilyLabel: string;
}) => {
  const createAttributePath = `#${router.generate(`pim_enrich_attribute_create`)}`;

  return (
    <div className="AknGridContainer-noData">
      <div className="AknGridContainer-noDataImage AknGridContainer-noDataImage--asset-family" />
      <div className="AknGridContainer-noDataTitle">
        {__('pim_asset_manager.asset.product.no_attribute.title', {
          entityLabel: assetFamilyLabel,
        })}
      </div>
      <div className="AknGridContainer-noDataSubtitle">
        {__('pim_asset_manager.asset.product.no_attribute.subtitle')}
        <a
          href={createAttributePath}
          title={__('pim_asset_manager.asset.product.no_attribute.link')}
          onClick={event => {
            event.preventDefault();

            onRedirectAttributeCreation();

            return false;
          }}
        >
          {__('pim_asset_manager.asset.product.no_attribute.link')}
        </a>
      </div>
    </div>
  );
};

export default NoAttribute;
