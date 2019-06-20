import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/product/attribute';

const NoEnoughItems = ({
  productCount,
  totalCount,
  selectedAttribute,
  showMore,
}: {
  productCount: number;
  totalCount: number;
  selectedAttribute: NormalizedAttribute | null;
  showMore: () => void;
}) => {
  if (null === selectedAttribute) {
    return null;
  }

  if (productCount >= totalCount) {
    return null;
  }

  const subtitle =
    true === selectedAttribute.useable_as_grid_filter
      ? 'pim_asset_manager.asset.product.not_enough_items.subtitle.usable_in_grid'
      : 'pim_asset_manager.asset.product.not_enough_items.subtitle.not_usable_in_grid';

  return (
    <React.Fragment>
      <div className="AknGridContainer-notEnoughDataTitle">
        {__('pim_asset_manager.asset.product.not_enough_items.title', {
          productCount: productCount,
          totalCount: totalCount,
        })}
      </div>
      <div className="AknGridContainer-notEnoughDataSubtitle">{__(subtitle)}</div>
      {true === selectedAttribute.useable_as_grid_filter ? (
        <button className="AknButton AknButton--big AknButton--apply AknButton--centered" onClick={() => showMore()}>
          {__('pim_asset_manager.asset.product.not_enough_items.button')}
        </button>
      ) : null}
      <img className="AknImage--centeredWithMargin" src="/bundles/pimui/images/illustration_scroll.svg" />
    </React.Fragment>
  );
};

export default NoEnoughItems;
