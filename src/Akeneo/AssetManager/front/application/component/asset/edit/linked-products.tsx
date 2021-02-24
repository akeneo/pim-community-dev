import React from 'react';
import {connect} from 'react-redux';
import styled from 'styled-components';
import {SectionTitle} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {EditState} from 'akeneoassetmanager/application/reducer/asset/edit';
import {NormalizedProduct} from 'akeneoassetmanager/domain/model/product/product';
import {Subsection} from 'akeneoassetmanager/application/component/app/subsection';
import {Product} from 'akeneoassetmanager/application/component/asset/edit/linked-products/product';
import {NoResults} from 'akeneoassetmanager/application/component/asset/edit/linked-products/no-results';
import {AttributeButton} from 'akeneoassetmanager/application/component/asset/edit/linked-products/attribute-button';
import Dropdown, {DropdownElement} from 'akeneoassetmanager/application/component/app/dropdown';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/product/attribute';
import {attributeSelected} from 'akeneoassetmanager/application/action/product/attribute';
import {denormalizeAttributeCode} from 'akeneoassetmanager/domain/model/attribute/code';
import {getLabelInCollection} from 'akeneoassetmanager/domain/model/label-collection';
import {Limit} from 'akeneoassetmanager/application/component/asset/edit/linked-products/limit';

const Grid = styled.div`
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  grid-gap: 20px;
  margin: 20px 0;
`;

interface StateProps {
  assetCode: string;
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
  assetCode,
  products,
  totalCount,
  isLoaded,
  context,
  attributes,
  selectedAttribute,
  events,
}: StateProps & DispatchProps) => {
  const translate = useTranslate();

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
        label: getLabelInCollection(attribute.labels, context.locale, true, attribute.code),
        original: attribute,
      })),
    [attributes, context.locale]
  );

  if (!isLoaded) {
    return (
      <Subsection>
        <SectionTitle sticky={192}>
          <SectionTitle.Title>{translate('pim_asset_manager.asset.enrich.product_subsection')}</SectionTitle.Title>
        </SectionTitle>
      </Subsection>
    );
  }

  return (
    <Subsection>
      <SectionTitle sticky={192}>
        <SectionTitle.Title>{translate('pim_asset_manager.asset.enrich.product_subsection')}</SectionTitle.Title>
        <SectionTitle.Spacer />
        <SectionTitle.Information>
          {translate('pim_asset_manager.result_counter', {count: totalCount}, totalCount)}
        </SectionTitle.Information>
        {null !== selectedAttribute && (
          <>
            <SectionTitle.Separator />
            <Dropdown
              elements={attributesAsDropdownElements}
              selectedElement={selectedAttribute.code}
              label={translate('pim_asset_manager.asset.product.attribute')}
              onSelectionChange={handleSelectedAttributeChange}
              ButtonView={AttributeButton}
              isOpenLeft={true}
            />
          </>
        )}
      </SectionTitle>
      <Grid>
        {products.map(product => (
          <Product key={product.id} product={product} locale={context.locale} />
        ))}
      </Grid>
      {products.length === 0 && <NoResults message={translate('pim_asset_manager.asset.no_linked_products')} />}
      <Limit
        assetCode={assetCode}
        productCount={products.length}
        totalCount={totalCount}
        attribute={selectedAttribute}
      />
    </Subsection>
  );
};

export default connect(
  (state: EditState): StateProps => {
    return {
      assetCode: state.form.data.code,
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
