import * as React from 'react';
import __ from 'akeneoreferenceentity/tools/translator';
import {NormalizedAttribute} from 'akeneoreferenceentity/domain/model/product/attribute';

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
  if (null === selectedAttribute || false === selectedAttribute.useable_as_grid_filter) {
    return null;
  }

  if (productCount >= totalCount) {
    return null;
  }

  return (
    <React.Fragment>
      <div className="AknGridContainer-notEnoughDataTitle">
        {__('pim_reference_entity.record.product.not_enough_items.title', {
          productCount: productCount,
          totalCount: totalCount,
        })}
      </div>
      <div className="AknGridContainer-notEnoughDataSubtitle">
        {__('pim_reference_entity.record.product.not_enough_items.subtitle')}
      </div>
      <button className="AknButton AknButton--big AknButton--apply AknButton--centered" onClick={() => showMore()}>
        {__('pim_reference_entity.record.product.not_enough_items.button')}
      </button>
      <img className="AknImage--centered AknImage-marginTop" src="/bundles/pimui/images/illustration_scroll.svg" />
    </React.Fragment>
  );
};

export default NoEnoughItems;
