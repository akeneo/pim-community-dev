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
import {
  createCode,
  NormalizedCode as NormalizedAttributeCode,
} from 'akeneoreferenceentity/domain/model/product/attribute/code';
import {NormalizedIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import {NormalizedAttribute} from 'akeneoreferenceentity/domain/model/product/attribute';
import NoAttribute from 'akeneoreferenceentity/application/component/record/edit/product/no-attribute';
import Key from 'akeneoreferenceentity/tools/key';
import ItemsCounter from 'akeneoreferenceentity/application/component/record/index/items-counter';

interface StateProps {
  context: {
    locale: string;
    channel: string;
  };
  products: ProductModel[];
  totalCount: number;
  attributes: DropdownElement[];
  selectedAttribute: NormalizedAttributeCode | null;
  recordCode: NormalizedCode;
  referenceEntityIdentifier: NormalizedIdentifier;
}

interface DispatchProps {
  events: {
    onLinkedAttributeChange: (attributeCode: string) => void;
    onRedirectToProduct: (product: ProductModel) => void;
    onRedirectAttributeCreation: () => void;
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


{/* <div className="AknFilterBox AknFilterBox--search">
  <div className="AknFilterBox-list filter-box">
    <SearchField value={userSearch} onChange={this.props.onSearchUpdated} changeThreshold={250} />
    <ItemsCounter count={grid.matchesCount} />
    <div className="AknFilterBox-filterContainer AknFilterBox-filterContainer--inline">
      {Object.keys(filterViews).map((attributeCode: NormalizedAttributeIdentifier) => {
        const View = filterViews[attributeCode].view;
        const attribute = filterViews[attributeCode].attribute;
        const filter = grid.filters.find((filter: Filter) => filter.field === getAttributeFilterKey(attribute));

        return (
          <div
            key={attribute.getCode().stringValue()}
            className="AknFilterBox-filter AknFilterBox-filter--relative AknFilterBox-filter--smallMargin"
            data-attribute={attribute.getCode().stringValue()}
            data-type={attribute.getType()}
          >
            <View attribute={attribute} filter={filter} onFilterUpdated={onFilterUpdated} />
          </div>
        );
      })}
    </div>
    <CompletenessFilter value={completenessValue} onChange={this.props.onCompletenessFilterUpdated} />
  </div>
</div> */}

class Product extends React.Component<StateProps & DispatchProps> {
  props: StateProps & DispatchProps;

  render() {
    const selectedAttribute = this.props.attributes.find(
      (attribute: DropdownElement) => attribute.identifier === this.props.selectedAttribute
    );

    return (
      <React.Fragment>
        {0 < this.props.attributes.length ? (
          <React.Fragment>
            <div className="AknFilterBox AknFilterBox--search">
              <div className="AknFilterBox-list">
                  <span className="AknFilterBox-title">
                    {__('pim_reference_entity.record.product.title')}
                </span>
                <ItemsCounter count={this.props.totalCount} />
                {null !== this.props.selectedAttribute ? (
                  <div className="AknFilterBox-filterContainer AknFilterBox-filterContainer--inline">
                    <div className="AknFilterBox-filter AknFilterBox-filter--relative AknFilterBox-filter--smallMargin">
                      <Dropdown
                        elements={this.props.attributes}
                        selectedElement={this.props.selectedAttribute}
                        label={__('pim_reference_entity.record.product.attribute')}
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
                {null !== this.props.selectedAttribute && undefined !== selectedAttribute ? (
                  <NoResult
                    entityLabel={selectedAttribute.label}
                    title="pim_reference_entity.record.product.no_product.title"
                    subtitle="pim_reference_entity.record.product.no_product.subtitle"
                    type="product"
                  />
                ) : null}
              </React.Fragment>
            )}
          </React.Fragment>
        ) : (
          <NoAttribute
            referenceEntityLabel={this.props.referenceEntityIdentifier}
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
      },
    };
  }
)(Product);
