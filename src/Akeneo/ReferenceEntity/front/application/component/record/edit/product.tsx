import * as React from 'react';
import {connect} from 'react-redux';
import {EditState} from 'akeneoreferenceentity/application/reducer/record/edit';
// import denormalizeRecord from 'akeneoreferenceentity/application/denormalizer/record';
import __ from 'akeneoreferenceentity/tools/translator';
import ProductModel, {denormalizeProduct, NormalizedProduct} from 'akeneoreferenceentity/domain/model/product/product';
import ItemView from 'akeneoreferenceentity/application/component/record/edit/product/item';
import {redirectToProduct} from 'akeneoreferenceentity/application/action/product/router';
import Dropdown, {DropdownElement} from 'akeneoreferenceentity/application/component/app/dropdown';
import {getLabel} from 'pimui/js/i18n';
import {attributeSelected} from 'akeneoreferenceentity/application/action/product/attribute';

interface StateProps {
  context: {
    locale: string;
    channel: string;
  };
  products: ProductModel[],
  attributes: any[],
  selectedAttribute: string | null
}

interface DispatchProps {
  events: {
    onLinkedAttributeChange: (attributeCode: string) => void;
    onRedirectToProduct: (product: ProductModel) => void;
  };
}

class Product extends React.Component<StateProps & DispatchProps> {
  props: StateProps & DispatchProps;

  render() {
    return (
      <React.Fragment>
        <header className="AknSubsection-title">
          <span className="group-label">{__('pim_reference_entity.record.product.title')}</span>
          {null !== this.props.selectedAttribute ? (
            <Dropdown
              elements={this.props.attributes}
              selectedElement={this.props.selectedAttribute}
              label={__('pim_reference_entity.record.product.attribute')}
              onSelectionChange={(selectedElement: DropdownElement) => {
                this.props.events.onLinkedAttributeChange(selectedElement.identifier)
              }}
              isOpenLeft={true}
            />
          ) : null}
        </header>
        <div className="AknSubsection">
          <div className="AknGrid--gallery">
            <div className="AknGridContainer">
              <div className="AknGrid">
                <div className="AknGrid-body">
                  {this.props.products.map((product: ProductModel) =>
                    <ItemView
                      key={product.getIdentifier().stringValue()}
                      product={product}
                      locale={this.props.context.locale}
                      onRedirectToProduct={this.props.events.onRedirectToProduct}
                    />
                  )}
                </div>
              </div>
            </div>
          </div>
        </div>
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
      products: state.products.products.map((normalizedProduct: NormalizedProduct) => denormalizeProduct(normalizedProduct)),
      attributes: state.products.attributes.map((attribute: any) => ({
        identifier: attribute.code,
        label: getLabel(attribute.labels, state.user.catalogLocale, attribute.code),
        original: attribute
      })),
      selectedAttribute: state.products.selectedAttribute
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        onLinkedAttributeChange: (attributeCode: string) => {
          dispatch(attributeSelected(attributeCode));
          // dispatch(recordLabelUpdated(value, locale));
        },
        onRedirectToProduct: (product: ProductModel) => {
          dispatch(redirectToProduct(product));
        },
      },
    };
  }
)(Product);
