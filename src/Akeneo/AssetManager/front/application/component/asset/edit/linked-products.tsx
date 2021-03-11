import React from 'react';
import {connect} from 'react-redux';
import styled from 'styled-components';
import {CardGrid, getColor, getFontSize, ProductsIllustration, SectionTitle} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {Section} from '@akeneo-pim-community/shared';
import {EditState} from 'akeneoassetmanager/application/reducer/asset/edit';
import {NormalizedProduct} from 'akeneoassetmanager/domain/model/product/product';
import {ProductCard} from 'akeneoassetmanager/application/component/asset/edit/linked-products/ProductCard';
import {AttributeButton} from 'akeneoassetmanager/application/component/asset/edit/linked-products/attribute-button';
import Dropdown, {DropdownElement} from 'akeneoassetmanager/application/component/app/dropdown';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/product/attribute';
import {attributeSelected} from 'akeneoassetmanager/application/action/product/attribute';
import {denormalizeAttributeCode} from 'akeneoassetmanager/domain/model/attribute/code';
import {getLabelInCollection} from 'akeneoassetmanager/domain/model/label-collection';
import {Limit} from 'akeneoassetmanager/application/component/asset/edit/linked-products/limit';

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

const NoResults = styled.div`
  display: flex;
  flex-direction: column;
  align-items: center;
  color: ${getColor('grey', 140)};
  font-size: ${getFontSize('big')};
`;

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
      <Section>
        <SectionTitle sticky={192}>
          <SectionTitle.Title>{translate('pim_asset_manager.asset.enrich.product_subsection')}</SectionTitle.Title>
        </SectionTitle>
      </Section>
    );
  }

  return (
    <Section>
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
      {0 === products.length ? (
        <NoResults>
          <ProductsIllustration size={128} />
          {translate('pim_asset_manager.asset.no_linked_products')}
        </NoResults>
      ) : (
        <CardGrid>
          {products.map(product => (
            <ProductCard key={product.id} product={product} locale={context.locale} />
          ))}
        </CardGrid>
      )}
      <Limit
        assetCode={assetCode}
        productCount={products.length}
        totalCount={totalCount}
        attribute={selectedAttribute}
      />
    </Section>
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
