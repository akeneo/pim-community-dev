import React from 'react';
import {connect} from 'react-redux';
import {CardGrid, SectionTitle, Key} from 'akeneo-design-system';
import {Section} from '@akeneo-pim-community/shared';
import {EditState} from 'akeneoreferenceentity/application/reducer/record/edit';
import __ from 'akeneoreferenceentity/tools/translator';
import ProductModel, {denormalizeProduct, NormalizedProduct} from 'akeneoreferenceentity/domain/model/product/product';
import {ProductCard} from 'akeneoreferenceentity/application/component/record/edit/product/ProductCard';
import {redirectToProduct, redirectToAttributeCreation} from 'akeneoreferenceentity/application/action/product/router';
import Dropdown, {DropdownElement} from 'akeneoreferenceentity/application/component/app/dropdown';
import {getLabel} from 'pimui/js/i18n';
import {attributeSelected} from 'akeneoreferenceentity/application/action/product/attribute';
import NoResult from 'akeneoreferenceentity/application/component/app/no-result';
import {NormalizedCode} from 'akeneoreferenceentity/domain/model/record/code';
import {
  createCode,
  NormalizedCode as NormalizedAttributeCode,
} from 'akeneoreferenceentity/domain/model/product/attribute/code';
import {NormalizedIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import {NormalizedAttribute} from 'akeneoreferenceentity/domain/model/product/attribute';
import NoAttribute from 'akeneoreferenceentity/application/component/record/edit/product/no-attribute';
import {redirectToProductGrid} from 'akeneoreferenceentity/application/event/router';
import NotEnoughItems from 'akeneoreferenceentity/application/component/record/edit/product/not-enough-items';

interface StateProps {
  context: {
    locale: string;
    channel: string;
  };
  products: ProductModel[];
  totalCount: number;
  attributes: DropdownElement[];
  selectedAttribute: NormalizedAttribute | null;
  recordCode: NormalizedCode;
  referenceEntityIdentifier: NormalizedIdentifier;
}

interface DispatchProps {
  events: {
    onLinkedAttributeChange: (attributeCode: string) => void;
    onRedirectToProduct: (product: ProductModel) => void;
    onRedirectAttributeCreation: () => void;
    onRedirectToProductGrid: (selectedAttribute: NormalizedAttributeCode, recordCode: NormalizedCode) => void;
  };
}

const AttributeButtonView = ({selectedElement, onClick}: {selectedElement: DropdownElement; onClick: () => void}) => (
  <div
    className="AknActionButton AknActionButton--light AknActionButton--withoutBorder"
    data-identifier={selectedElement.identifier}
    onClick={onClick}
    tabIndex={0}
    onKeyPress={event => {
      if (Key.Space === event.key) onClick();
    }}
  >
    {__('pim_reference_entity.record.product.dropdown.attribute')}
    :&nbsp;
    <span className="AknActionButton-highlight" data-identifier={selectedElement.identifier}>
      {selectedElement.label}
    </span>
    <span className="AknActionButton-caret" />
  </div>
);

class Product extends React.Component<StateProps & DispatchProps> {
  props: StateProps & DispatchProps;

  render() {
    const selectedDropdownAttribute = this.props.attributes.find((attribute: DropdownElement) => {
      if (null === this.props.selectedAttribute) {
        return false;
      }

      return attribute.identifier === this.props.selectedAttribute.code;
    });

    return 0 < this.props.attributes.length ? (
      <Section>
        <SectionTitle>
          <SectionTitle.Title>{__('pim_reference_entity.record.product.title')}</SectionTitle.Title>
          <SectionTitle.Spacer />
          <SectionTitle.Information>
            {__('pim_reference_entity.grid.counter', {count: this.props.totalCount}, this.props.totalCount)}
          </SectionTitle.Information>
          {null !== this.props.selectedAttribute && (
            <>
              <SectionTitle.Separator />
              <Dropdown
                elements={this.props.attributes}
                selectedElement={(this.props.selectedAttribute as NormalizedAttribute).code}
                label={__('pim_reference_entity.record.product.attribute')}
                onSelectionChange={(selectedElement: DropdownElement) => {
                  this.props.events.onLinkedAttributeChange(selectedElement.identifier);
                }}
                ButtonView={AttributeButtonView}
                isOpenLeft={true}
              />
            </>
          )}
        </SectionTitle>
        {0 < this.props.products.length && null !== this.props.selectedAttribute ? (
          <CardGrid>
            {this.props.products.map((product: ProductModel) => (
              <ProductCard
                key={product.getIdentifier().stringValue()}
                product={product}
                locale={this.props.context.locale}
              />
            ))}
          </CardGrid>
        ) : (
          <React.Fragment>
            {undefined !== selectedDropdownAttribute ? (
              <NoResult
                entityLabel={selectedDropdownAttribute.label}
                title="pim_reference_entity.record.product.no_product.title"
                subtitle="pim_reference_entity.record.product.no_product.subtitle"
                type="product"
              />
            ) : null}
          </React.Fragment>
        )}
        <NotEnoughItems
          productCount={this.props.products.length}
          totalCount={this.props.totalCount}
          selectedAttribute={this.props.selectedAttribute as NormalizedAttribute}
          showMore={() =>
            this.props.events.onRedirectToProductGrid(
              (this.props.selectedAttribute as NormalizedAttribute).code,
              this.props.recordCode
            )
          }
        />
      </Section>
    ) : (
      <NoAttribute
        referenceEntityLabel={this.props.referenceEntityIdentifier}
        onRedirectAttributeCreation={this.props.events.onRedirectAttributeCreation}
      />
    );
  }
}

export default connect(
  (state: EditState): StateProps => {
    const locale = state.user.catalogLocale;

    return {
      context: {
        locale: locale,
        channel: state.user.catalogChannel,
      },
      products: state.products.products.map((normalizedProduct: NormalizedProduct) =>
        denormalizeProduct(normalizedProduct)
      ),
      totalCount: state.products.totalCount,
      attributes: state.products.attributes.map((attribute: NormalizedAttribute) => ({
        identifier: attribute.code,
        label: getLabel(attribute.labels, state.user.catalogLocale, attribute.code),
        original: attribute,
      })),
      selectedAttribute: state.products.selectedAttribute,
      recordCode: state.form.data.code,
      referenceEntityIdentifier: state.form.data.reference_entity_identifier,
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        onLinkedAttributeChange: (attributeCode: string) => {
          dispatch(attributeSelected(createCode(attributeCode)));
        },
        onRedirectToProduct: (product: ProductModel) => {
          dispatch(redirectToProduct(product));
        },
        onRedirectAttributeCreation: () => {
          dispatch(redirectToAttributeCreation());
        },
        onRedirectToProductGrid: (selectedAttribute: NormalizedAttributeCode, recordCode: NormalizedCode) => {
          dispatch(redirectToProductGrid(selectedAttribute, recordCode));
        },
      },
    };
  }
)(Product);
