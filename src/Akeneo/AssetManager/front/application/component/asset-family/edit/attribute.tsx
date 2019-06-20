import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import {connect} from 'react-redux';
import {attributeCreationStart} from 'akeneoassetmanager/domain/event/attribute/create';
import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import {CreateState} from 'akeneoassetmanager/application/reducer/attribute/create';
import CreateAttributeModal from 'akeneoassetmanager/application/component/attribute/create';
import ManageOptionsView from 'akeneoassetmanager/application/component/attribute/edit/option';
import AttributeIdentifier from 'akeneoassetmanager/domain/model/attribute/identifier';
import AssetFamily, {
  denormalizeAssetFamily,
} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {attributeEditionStartByIdentifier} from 'akeneoassetmanager/application/action/attribute/edit';
import AttributeEditForm from 'akeneoassetmanager/application/component/attribute/edit';
import Header from 'akeneoassetmanager/application/component/asset-family/edit/header';
import {breadcrumbConfiguration} from 'akeneoassetmanager/application/component/asset-family/edit';
import denormalizeAttribute from 'akeneoassetmanager/application/denormalizer/attribute/attribute';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {getAttributeIcon} from 'akeneoassetmanager/application/configuration/attribute';
import Key from 'akeneoassetmanager/tools/key';
import ErrorBoundary from 'akeneoassetmanager/application/component/app/error-boundary';
import {EditOptionState} from 'akeneoassetmanager/application/reducer/attribute/type/option';
import {canEditLocale, canEditAssetFamily} from 'akeneoassetmanager/application/reducer/right';

const securityContext = require('pim/security-context');

interface StateProps {
  context: {
    locale: string;
  };
  rights: {
    locale: {
      edit: boolean;
    };
    attribute: {
      create: boolean;
      edit: boolean;
      delete: boolean;
    };
  };
  assetFamily: AssetFamily;
  createAttribute: CreateState;
  editAttribute: boolean;
  options: EditOptionState;
  attributes: NormalizedAttribute[];
  firstLoading: boolean;
}
interface DispatchProps {
  events: {
    onAttributeCreationStart: () => void;
    onAttributeEdit: (attributeIdentifier: AttributeIdentifier) => void;
  };
}
interface CreateProps extends StateProps, DispatchProps {}

const renderSystemAttribute = (type: string, identifier: string) => {
  return (
    <div
      className="AknFieldContainer"
      data-placeholder="false"
      data-identifier={`system_asset_${identifier}`}
      data-type={type}
    >
      <div className="AknFieldContainer-header AknFieldContainer-header--light">
        <label
          className="AknFieldContainer-label AknFieldContainer-label--withImage"
          htmlFor={`pim_asset_manager.asset_family.properties.system_asset_${identifier}`}
        >
          <img className="AknFieldContainer-labelImage" src={`bundles/pimui/images/attribute/icon-${type}.svg`} />
          <span>{identifier}</span>
        </label>
      </div>
      <div className="AknFieldContainer-inputContainer">
        <input
          type="text"
          autoComplete="off"
          tabIndex={-1}
          id={`pim_asset_manager.asset_family.properties.system_asset_${identifier}`}
          className="AknTextField AknTextField--light AknTextField--disabled"
          value={__(`pim_asset_manager.attribute.default.${identifier}`)}
          readOnly
        />
      </div>
    </div>
  );
};

const renderSystemAttributes = () => {
  return <React.Fragment>{renderSystemAttribute('text', 'code')}</React.Fragment>;
};

const renderAttributePlaceholders = () => {
  return Array(8)
    .fill('placeholder')
    .map((attributeIdentifier, key) => (
      <div key={key} className="AknFieldContainer" data-placeholder="true">
        <div className="AknFieldContainer-header AknFieldContainer-header--light">
          <label
            className="AknFieldContainer-label AknFieldContainer-label--withImage AknLoadingPlaceHolder"
            htmlFor={`pim_asset_manager.asset_family.properties.${attributeIdentifier}_${key}`}
          >
            <img className="AknFieldContainer-labelImage" src={`bundles/pimui/images/attribute/icon-text.svg`} />
            <span>
              {__(`pim_asset_manager.attribute.type.text`)} {`(${__('pim_asset_manager.attribute.is_required')})`}
            </span>
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer AknLoadingPlaceHolder">
          <input
            type="text"
            autoComplete="off"
            id={`pim_asset_manager.asset_family.properties.${attributeIdentifier}_${key}`}
            className="AknTextField AknTextField--transparent"
          />
          <button className="AknIconButton AknIconButton--trash" />
          <button className="AknIconButton AknIconButton--edit" />
        </div>
      </div>
    ));
};

interface AttributeViewProps {
  attribute: NormalizedAttribute;
  onAttributeEdit: (attributeIdentifier: AttributeIdentifier) => void;
  locale: string;
  rights: {
    locale: {
      edit: boolean;
    };
    attribute: {
      create: boolean;
      edit: boolean;
      delete: boolean;
    };
  };
}

class AttributeView extends React.Component<AttributeViewProps> {
  public shouldComponentUpdate(nextProps: AttributeViewProps) {
    return (
      nextProps.attribute.labels[nextProps.locale] !== this.props.attribute.labels[this.props.locale] ||
      nextProps.attribute.is_required !== this.props.attribute.is_required
    );
  }

  render() {
    const {onAttributeEdit, locale, rights} = this.props;
    const attribute = denormalizeAttribute(this.props.attribute);
    const icon = getAttributeIcon(attribute.getType());

    return (
      <div
        className="AknFieldContainer"
        data-placeholder="false"
        data-identifier={attribute.getCode().stringValue()}
        data-type={attribute.getType()}
      >
        <div className="AknFieldContainer-header AknFieldContainer-header--light">
          <label
            className="AknFieldContainer-label AknFieldContainer-label--withImage"
            htmlFor={`pim_asset_manager.asset_family.properties.${attribute.getCode().stringValue()}`}
          >
            <img className="AknFieldContainer-labelImage" src={icon} />
            <span>
              {attribute.getCode().stringValue()}{' '}
              {attribute.isRequired ? `(${__('pim_asset_manager.attribute.is_required')})` : ''}
            </span>
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <input
            type="text"
            autoComplete="off"
            id={`pim_asset_manager.asset_family.properties.${attribute.getCode().stringValue()}`}
            className="AknTextField AknTextField--light AknTextField--disabled"
            value={attribute.getLabel(locale)}
            readOnly
            tabIndex={-1}
          />
          {rights.attribute.edit ? (
            <button
              className="AknIconButton AknIconButton--edit"
              onClick={() => onAttributeEdit(attribute.getIdentifier())}
              onKeyPress={(event: React.KeyboardEvent<HTMLButtonElement>) => {
                if (Key.Space === event.key) onAttributeEdit(attribute.getIdentifier());
              }}
            />
          ) : (
            <button
              className="AknIconButton AknIconButton--view"
              onClick={() => onAttributeEdit(attribute.getIdentifier())}
              onKeyPress={(event: React.KeyboardEvent<HTMLButtonElement>) => {
                if (Key.Space === event.key) onAttributeEdit(attribute.getIdentifier());
              }}
            />
          )}
        </div>
      </div>
    );
  }
}

class AttributesView extends React.Component<CreateProps> {
  render() {
    return (
      <React.Fragment>
        <Header
          label={this.props.assetFamily.getLabel(this.props.context.locale)}
          image={this.props.assetFamily.getImage()}
          primaryAction={(defaultFocus: React.RefObject<any>) => {
            return this.props.rights.attribute.create ? (
              <button
                className="AknButton AknButton--action"
                onClick={this.props.events.onAttributeCreationStart}
                ref={defaultFocus}
                tabIndex={0}
              >
                {__('pim_asset_manager.attribute.button.add')}
              </button>
            ) : null;
          }}
          secondaryActions={() => null}
          withLocaleSwitcher={true}
          withChannelSwitcher={false}
          isDirty={false}
          breadcrumbConfiguration={breadcrumbConfiguration}
          displayActions={this.props.rights.attribute.create}
        />
        <div className="AknSubsection">
          <header className="AknSubsection-title AknSubsection-title--sticky" style={{top: '192px'}}>
            <span className="group-label">{__('pim_asset_manager.asset_family.attribute.title')}</span>
          </header>
          {this.props.firstLoading || 0 < this.props.attributes.length ? (
            <div className="AknSubsection-container">
              <div className="AknFormContainer AknFormContainer--withPadding">
                {renderSystemAttributes()}
                {this.props.firstLoading ? (
                  renderAttributePlaceholders()
                ) : (
                  <React.Fragment>
                    {this.props.attributes.map((attribute: NormalizedAttribute) => (
                      <ErrorBoundary
                        key={attribute.identifier}
                        errorMessage={__('pim_asset_manager.asset_family.attribute.error.render_list')}
                      >
                        <AttributeView
                          attribute={attribute}
                          onAttributeEdit={this.props.events.onAttributeEdit}
                          locale={this.props.context.locale}
                          rights={this.props.rights}
                        />
                      </ErrorBoundary>
                    ))}
                  </React.Fragment>
                )}
              </div>
              {this.props.editAttribute ? <AttributeEditForm rights={this.props.rights} /> : null}
            </div>
          ) : (
            <React.Fragment>
              <div className="AknSubsection-container">
                <div className="AknFormContainer AknFormContainer--withPadding">{renderSystemAttributes()}</div>
              </div>
              <div className="AknGridContainer-noData AknGridContainer-noData--small">
                <div className="AknGridContainer-noDataTitle">
                  {__('pim_asset_manager.attribute.no_data.title', {
                    entityLabel: this.props.assetFamily.getLabel(this.props.context.locale),
                  })}
                </div>
                <div className="AknGridContainer-noDataSubtitle">
                  {__('pim_asset_manager.attribute.no_data.subtitle')}
                </div>
                <button className="AknButton AknButton--action" onClick={this.props.events.onAttributeCreationStart}>
                  {__('pim_asset_manager.attribute.button.add')}
                </button>
              </div>
            </React.Fragment>
          )}
          {this.props.createAttribute.active ? <CreateAttributeModal /> : null}
          {this.props.options.isActive ? <ManageOptionsView rights={this.props.rights} /> : null}
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
      },
      rights: {
        locale: {
          edit: canEditLocale(state.right.locale, locale),
        },
        attribute: {
          create:
            securityContext.isGranted('akeneo_assetmanager_attribute_create') &&
            canEditAssetFamily(state.right.assetFamily, state.form.data.identifier),
          edit:
            securityContext.isGranted('akeneo_assetmanager_attribute_edit') &&
            canEditAssetFamily(state.right.assetFamily, state.form.data.identifier),
          delete:
            securityContext.isGranted('akeneo_assetmanager_attribute_edit') &&
            securityContext.isGranted('akeneo_assetmanager_attribute_delete') &&
            canEditAssetFamily(state.right.assetFamily, state.form.data.identifier),
        },
      },
      assetFamily: denormalizeAssetFamily(state.form.data),
      createAttribute: state.createAttribute,
      editAttribute: state.attribute.isActive,
      options: state.options,
      firstLoading: null === state.attributes.attributes,
      attributes: null !== state.attributes.attributes ? state.attributes.attributes : [],
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        onAttributeCreationStart: () => {
          dispatch(attributeCreationStart());
        },
        onAttributeEdit: (attributeIdentifier: AttributeIdentifier) => {
          dispatch(attributeEditionStartByIdentifier(attributeIdentifier));
        },
      },
    };
  }
)(AttributesView);
