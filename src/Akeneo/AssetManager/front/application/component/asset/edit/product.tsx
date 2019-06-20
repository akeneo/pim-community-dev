import * as React from 'react';
import {connect} from 'react-redux';
import {EditState} from 'akeneoassetmanager/application/reducer/asset/edit';
import __ from 'akeneoassetmanager/tools/translator';
import ProductModel, {denormalizeProduct, NormalizedProduct} from 'akeneoassetmanager/domain/model/product/product';
import ItemView from 'akeneoassetmanager/application/component/asset/edit/product/item';
import {redirectToProduct, redirectToAttributeCreation} from 'akeneoassetmanager/application/action/product/router';
import Dropdown, {DropdownElement} from 'akeneoassetmanager/application/component/app/dropdown';
import {getLabel} from 'pimui/js/i18n';
import {attributeSelected} from 'akeneoassetmanager/application/action/product/attribute';
import NoResult from 'akeneoassetmanager/application/component/app/no-result';
import {NormalizedCode} from 'akeneoassetmanager/domain/model/asset/code';
import {
  createCode,
  NormalizedCode as NormalizedAttributeCode,
} from 'akeneoassetmanager/domain/model/product/attribute/code';
import {NormalizedIdentifier} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/product/attribute';
import NoAttribute from 'akeneoassetmanager/application/component/asset/edit/product/no-attribute';
import Key from 'akeneoassetmanager/tools/key';
import ItemsCounter from 'akeneoassetmanager/application/component/asset/index/items-counter';
import {redirectToProductGrid} from 'akeneoassetmanager/application/event/router';
import NotEnoughItems from 'akeneoassetmanager/application/component/asset/edit/product/not-enough-items';

interface StateProps {
  context: {
    locale: string;
    channel: string;
  };
  products: ProductModel[];
  totalCount: number;
  attributes: DropdownElement[];
  selectedAttribute: NormalizedAttribute | null;
  assetCode: NormalizedCode;
  assetFamilyIdentifier: NormalizedIdentifier;
}

interface DispatchProps {
  events: {
    onLinkedAttributeChange: (attributeCode: string) => void;
    onRedirectToProduct: (product: ProductModel) => void;
    onRedirectAttributeCreation: () => void;
    onRedirectToProductGrid: (selectedAttribute: NormalizedAttributeCode, assetCode: NormalizedCode) => void;
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
    {__('pim_asset_manager.asset.product.dropdown.attribute')}
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

    return (
      <React.Fragment>
        {0 < this.props.attributes.length ? (
          <React.Fragment>
            <div className="AknFilterBox AknFilterBox--search">
              <div className="AknFilterBox-list">
                <span className="AknFilterBox-title">{__('pim_asset_manager.asset.product.title')}</span>
                <ItemsCounter count={this.props.totalCount} inline={true} />
                {null !== this.props.selectedAttribute ? (
                  <div className="AknFilterBox-filterContainer AknFilterBox-filterContainer--inline">
                    <div className="AknFilterBox-filter AknFilterBox-filter--relative AknFilterBox-filter--smallMargin">
                      <Dropdown
                        elements={this.props.attributes}
                        selectedElement={(this.props.selectedAttribute as NormalizedAttribute).code}
                        label={__('pim_asset_manager.asset.product.attribute')}
                        onSelectionChange={(selectedElement: DropdownElement) => {
                          this.props.events.onLinkedAttributeChange(selectedElement.identifier);
                        }}
                        ButtonView={AttributeButtonView}
                        isOpenLeft={true}
                      />
                    </div>
                  </div>
                ) : null}
              </div>
            </div>
            {0 < this.props.products.length && null !== this.props.selectedAttribute ? (
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
                {undefined !== selectedDropdownAttribute ? (
                  <NoResult
                    entityLabel={selectedDropdownAttribute.label}
                    title="pim_asset_manager.asset.product.no_product.title"
                    subtitle="pim_asset_manager.asset.product.no_product.subtitle"
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
                  this.props.assetCode
                )
              }
            />
          </React.Fragment>
        ) : (
          <NoAttribute
            assetFamilyLabel={this.props.assetFamilyIdentifier}
            onRedirectAttributeCreation={this.props.events.onRedirectAttributeCreation}
          />
        )}
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
      totalCount: state.products.totalCount,
      attributes: state.products.attributes.map((attribute: NormalizedAttribute) => ({
        identifier: attribute.code,
        label: getLabel(attribute.labels, state.user.catalogLocale, attribute.code),
        original: attribute,
      })),
      selectedAttribute: state.products.selectedAttribute,
      assetCode: state.form.data.code,
      assetFamilyIdentifier: state.form.data.asset_family_identifier,
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
        onRedirectToProductGrid: (selectedAttribute: NormalizedAttributeCode, assetCode: NormalizedCode) => {
          dispatch(redirectToProductGrid(selectedAttribute, assetCode));
        },
      },
    };
  }
)(Product);
