import * as React from 'react';
import {connect} from 'react-redux';
import {EditState} from 'akeneoreferenceentity/application/reducer/record/edit';
import __ from 'akeneoreferenceentity/tools/translator';
import ProductModel, {denormalizeProduct, NormalizedProduct} from 'akeneoreferenceentity/domain/model/product/product';
import ItemView from 'akeneoreferenceentity/application/component/record/edit/product/item';
import {redirectToProduct, redirectToAttributeCreation} from 'akeneoreferenceentity/application/action/product/router';
import Dropdown, {DropdownElement} from 'akeneoreferenceentity/application/component/app/dropdown';
import {getLabel} from 'pimui/js/i18n';
import {attributeSelected} from 'akeneoreferenceentity/application/action/product/attribute';
import NoResult from 'akeneoreferenceentity/application/component/app/no-result';
import {NormalizedCode} from 'akeneoreferenceentity/domain/model/record/code';
import {createCode, NormalizedCode as NormalizedAttributeCode} from 'akeneoreferenceentity/domain/model/product/attribute/code';
import {NormalizedIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import {NormalizedAttribute} from 'akeneoreferenceentity/domain/model/product/attribute';
const router = require('pim/router');

interface StateProps {
  context: {
    locale: string;
    channel: string;
  };
  products: ProductModel[];
  attributes: NormalizedAttribute[];
  selectedAttribute: NormalizedAttributeCode | null;
  recordCode: NormalizedCode
  referenceEntityIdentifier: NormalizedIdentifier
}

interface DispatchProps {
  events: {
    onLinkedAttributeChange: (attributeCode: string) => void;
    onRedirectToProduct: (product: ProductModel) => void;
    onRedirectAttributeCreation: () => void;
  };
}

class Product extends React.Component<StateProps & DispatchProps> {
  props: StateProps & DispatchProps;

  render() {
    const createAttributePath = `#${router.generate(`pim_enrich_attribute_create`)}`;

    return (
      <React.Fragment>
        {0 < this.props.attributes.length ? (
          <React.Fragment>
            <header className="AknSubsection-title">
              <span className="group-label">{__('pim_reference_entity.record.product.title')}</span>
              {null !== this.props.selectedAttribute ? (
                <Dropdown
                  elements={this.props.attributes}
                  selectedElement={this.props.selectedAttribute}
                  label={__('pim_reference_entity.record.product.attribute')}
                  onSelectionChange={(selectedElement: DropdownElement) => {
                    this.props.events.onLinkedAttributeChange(selectedElement.identifier);
                  }}
                  isOpenLeft={true}
                  />
                  ) : null}
            </header>
            {0 < this.props.products.length && null !==  this.props.selectedAttribute ? (
              <div className="AknSubsection">
                <div className="AknGrid--gallery">
                  <div className="AknGridContainer">
                    <div className="AknGrid">
                      <div className="AknGrid-body">
                        {this.props.products.map((product: ProductModel) => (
                          <ItemView
                            key={product.getIdentifier().stringValue()}
                            product={product}
                            locale={this.props.context.locale}
                            onRedirectToProduct={this.props.events.onRedirectToProduct}
                          />
                        ))}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            ) : (
              <React.Fragment>
                {null !== this.props.selectedAttribute ? (
                  <NoResult
                    entityLabel={this.props.attributes.find((attribute: NormalizedAttribute) => attribute.code === this.props.selectedAttribute).label}
                    title="pim_reference_entity.record.product.no_product.title"
                    subtitle="pim_reference_entity.record.product.no_product.subtitle"
                    type="product"
                  />
                ) : null}
              </React.Fragment>
            )}
          </React.Fragment>
        ) : (
          <div className="AknGridContainer-noData">
            <div className="AknGridContainer-noDataImage AknGridContainer-noDataImage--reference-entity" />
            <div className="AknGridContainer-noDataTitle">
              {__('pim_reference_entity.record.product.no_attribute.title', {
                entityLabel: this.props.referenceEntityIdentifier,
              })}
            </div>
            <div className="AknGridContainer-noDataSubtitle">
              {__('pim_reference_entity.record.product.no_attribute.subtitle')}
              <a
                href={createAttributePath}
                title={__('pim_reference_entity.record.product.no_attribute.link')}
                onClick={event => {
                  event.preventDefault();

                  this.props.events.onRedirectAttributeCreation();

                  return false;
                }}
              >
                {__('pim_reference_entity.record.product.no_attribute.link')}
              </a>
            </div>
          </div>
        ) }
      </React.Fragment>
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
      attributes: state.products.attributes.map((attribute: any) => ({
        identifier: attribute.code,
        label: getLabel(attribute.labels, state.user.catalogLocale, attribute.code),
        original: attribute,
      })),
      selectedAttribute: state.products.selectedAttribute,
      recordCode: state.form.data.code,
      referenceEntityIdentifier: state.form.data.reference_entity_identifier
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
      },
    };
  }
)(Product);
