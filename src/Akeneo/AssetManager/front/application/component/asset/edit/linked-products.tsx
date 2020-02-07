import * as React from 'react';
import {connect} from 'react-redux';
import styled from 'styled-components';
import {EditState} from 'akeneoassetmanager/application/reducer/asset/edit';
import {NormalizedProduct} from 'akeneoassetmanager/domain/model/product/product';
import __ from 'akeneoassetmanager/tools/translator';
import {Subsection, SubsectionHeader} from 'akeneoassetmanager/application/component/app/subsection';
import {ItemsCounter} from 'akeneoassetmanager/application/component/asset/list/items-counter';
import {Product} from 'akeneoassetmanager/application/component/asset/edit/linked-products/product';
import {NoResults} from 'akeneoassetmanager/application/component/asset/edit/linked-products/no-results';

const Grid = styled.div`
  display: flex;
  flex-wrap: wrap;
  margin: 10px -10px 0;
`;

interface StateProps {
  context: {
    locale: string;
    channel: string;
  };
  products: NormalizedProduct[];
  totalCount: number;
  isLoaded: boolean;
}

const LinkedProducts = ({products, totalCount, isLoaded, context}: StateProps) => {
  if (!isLoaded) {
    return (
      <Subsection>
        <SubsectionHeader>
          <span>{__('pim_asset_manager.asset.enrich.product_subsection')}</span>
        </SubsectionHeader>
      </Subsection>
    );
  }

  return (
    <Subsection>
      <SubsectionHeader>
        <span>{__('pim_asset_manager.asset.enrich.product_subsection')}</span>
        <ItemsCounter count={totalCount} />
      </SubsectionHeader>
      <Grid>
        {products.map(product => (
          <Product key={product.id} product={product} locale={context.locale} />
        ))}
      </Grid>
      {products.length === 0 && <NoResults message={__('pim_asset_manager.asset.no_linked_products')} />}
    </Subsection>
  );
};

export default connect(
  (state: EditState): StateProps => {
    return {
      context: {
        locale: state.user.catalogLocale,
        channel: state.user.catalogChannel,
      },
      products: state.products.products,
      totalCount: state.products.totalCount,
      isLoaded: state.products.isLoaded,
    };
  }
)(LinkedProducts);
