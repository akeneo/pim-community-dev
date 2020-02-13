import * as React from 'react';
import {connect} from 'react-redux';
import styled from 'styled-components';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import {EditState} from 'akeneoassetmanager/application/reducer/asset/edit';
import {NormalizedProduct} from 'akeneoassetmanager/domain/model/product/product';
import {getLabel} from 'pimui/js/i18n';
import __ from 'akeneoassetmanager/tools/translator';
import {Subsection, SubsectionHeader} from 'akeneoassetmanager/application/component/app/subsection';
import {ItemsCounter} from 'akeneoassetmanager/application/component/asset/list/items-counter';
import {Product} from 'akeneoassetmanager/application/component/asset/edit/linked-products/product';
import {NoResults} from 'akeneoassetmanager/application/component/asset/edit/linked-products/no-results';
import {AttributeButton} from 'akeneoassetmanager/application/component/asset/edit/linked-products/attribute-button';
import Dropdown, {DropdownElement} from 'akeneoassetmanager/application/component/app/dropdown';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/product/attribute';
import {attributeSelected} from 'akeneoassetmanager/application/action/product/attribute';
import {denormalizeAttributeCode} from 'akeneoassetmanager/domain/model/attribute/code';

const Grid = styled.div`
  display: flex;
  flex-wrap: wrap;
  margin: 10px -10px 0;
`;

const SubsectionHeaderRight = styled.div`
  display: flex;
  align-items: center;

  .AknActionButton--light {
    padding-top: 0;
  }
`;

const SubsectionHeaderSeparator = styled.div`
  background: ${(props: ThemedProps<void>) => props.theme.color.grey80};
  display: block;
  height: 24px;
  margin: 0 5px 0 10px;
  width: 1px;
`;

interface StateProps {
  context: {
    locale: string;
    channel: string;
  };
  products: NormalizedProduct[];
  totalCount: number;
  isLoaded: boolean;
  attributes: NormalizedAttribute[];
  selectedAttribute: NormalizedAttribute | null;
}

interface DispatchProps {
  events: {
    onLinkedAttributeChange: (attributeCode: string) => void;
  };
}

const LinkedProducts = ({
  products,
  totalCount,
  isLoaded,
  context,
  attributes,
  selectedAttribute,
  events,
}: StateProps & DispatchProps) => {
  if (!isLoaded) {
    return (
      <Subsection>
        <SubsectionHeader>
          <span>{__('pim_asset_manager.asset.enrich.product_subsection')}</span>
        </SubsectionHeader>
      </Subsection>
    );
  }

  const handleSelectedAttributeChange = React.useCallback(
    (selectedElement: DropdownElement) => {
      events.onLinkedAttributeChange(selectedElement.identifier);
    },
    [events.onLinkedAttributeChange]
  );

  const attributesAsDropdownElements = React.useMemo(
    () =>
      attributes.map(attribute => ({
        identifier: attribute.code,
        label: getLabel(attribute.labels, context.locale, attribute.code),
        original: attribute,
      })),
    [attributes, context.locale]
  );

  return (
    <Subsection>
      <SubsectionHeader>
        <span>{__('pim_asset_manager.asset.enrich.product_subsection')}</span>
        <SubsectionHeaderRight>
          <ItemsCounter count={totalCount} />
          {null !== selectedAttribute && (
            <>
              <SubsectionHeaderSeparator />
              <Dropdown
                elements={attributesAsDropdownElements}
                selectedElement={selectedAttribute.code}
                label={__('pim_asset_manager.asset.product.attribute')}
                onSelectionChange={handleSelectedAttributeChange}
                ButtonView={AttributeButton}
                isOpenLeft={true}
              />
            </>
          )}
        </SubsectionHeaderRight>
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
      attributes: state.products.attributes,
      selectedAttribute: state.products.selectedAttribute,
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        onLinkedAttributeChange: (attributeCode: string) => {
          dispatch(attributeSelected(denormalizeAttributeCode(attributeCode)));
        },
      },
    };
  }
)(LinkedProducts);
