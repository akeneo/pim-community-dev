import React from 'react';
import styled from 'styled-components';
import {
  CardGrid,
  Dropdown,
  getColor,
  getFontSize,
  ProductsIllustration,
  SectionTitle,
  SwitcherButton,
  useBooleanState,
} from 'akeneo-design-system';
import {Section, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {ProductCard} from 'akeneoassetmanager/application/component/asset/edit/linked-products/ProductCard';
import {getLabelInCollection} from 'akeneoassetmanager/domain/model/label-collection';
import {Limit} from 'akeneoassetmanager/application/component/asset/edit/linked-products/limit';
import {ProductAttribute} from 'akeneoassetmanager/domain/model/product/attribute';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {useProductAttributes} from 'akeneoassetmanager/application/component/asset/edit/linked-products/useProductAttributes';
import {useLinkedProducts} from 'akeneoassetmanager/application/component/asset/edit/linked-products/useLinkedProducts';

type LinkedProductsProps = {
  assetFamilyIdentifier: AssetFamilyIdentifier;
  assetCode: AssetCode;
};

const NoResults = styled.div`
  display: flex;
  flex-direction: column;
  align-items: center;
  color: ${getColor('grey', 140)};
  font-size: ${getFontSize('big')};
`;

const LinkedProducts = ({assetFamilyIdentifier, assetCode}: LinkedProductsProps) => {
  const translate = useTranslate();
  const locale = useUserContext().get('catalogLocale');
  const channel = useUserContext().get('catalogScope');
  const [isDropdownOpen, openDropdown, closeDropdown] = useBooleanState();
  const [attributes, selectedAttribute, setSelectedAttribute] = useProductAttributes(assetFamilyIdentifier);
  const [products, totalCount] = useLinkedProducts(
    assetFamilyIdentifier,
    assetCode,
    selectedAttribute,
    channel,
    locale
  );

  const handleAttributeChange = (attributeCode: string) => () => {
    if (null !== attributes) {
      closeDropdown();

      const selectedAttribute = attributes.find(attribute => attribute.code === attributeCode);

      setSelectedAttribute(selectedAttribute ?? null);
    }
  };

  if (null === attributes || null === products) {
    return (
      <Section>
        <SectionTitle sticky={202}>
          <SectionTitle.Title>{translate('pim_asset_manager.asset.enrich.product_subsection')}</SectionTitle.Title>
        </SectionTitle>
      </Section>
    );
  }

  return (
    <Section>
      <SectionTitle sticky={202}>
        <SectionTitle.Title>{translate('pim_asset_manager.asset.enrich.product_subsection')}</SectionTitle.Title>
        <SectionTitle.Spacer />
        <SectionTitle.Information>
          {translate('pim_asset_manager.result_counter', {count: totalCount}, totalCount)}
        </SectionTitle.Information>
        {null !== selectedAttribute && (
          <>
            <SectionTitle.Separator />
            <Dropdown>
              <SwitcherButton
                label={translate('pim_asset_manager.asset.product.dropdown.attribute')}
                onClick={openDropdown}
              >
                {getLabelInCollection(selectedAttribute.labels, locale, true, selectedAttribute.code)}
              </SwitcherButton>
              {isDropdownOpen && (
                <Dropdown.Overlay onClose={closeDropdown}>
                  <Dropdown.Header>
                    <Dropdown.Title>{translate('pim_asset_manager.asset.product.attribute')}</Dropdown.Title>
                  </Dropdown.Header>
                  <Dropdown.ItemCollection>
                    {attributes.map((attribute: ProductAttribute) => (
                      <Dropdown.Item
                        key={attribute.code}
                        isActive={selectedAttribute.code === attribute.code}
                        onClick={handleAttributeChange(attribute.code)}
                      >
                        {getLabelInCollection(attribute.labels, locale, true, attribute.code)}
                      </Dropdown.Item>
                    ))}
                  </Dropdown.ItemCollection>
                </Dropdown.Overlay>
              )}
            </Dropdown>
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
            <ProductCard key={product.id} product={product} locale={locale} />
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

export {LinkedProducts};
