import React, {memo} from 'react';
import {connect} from 'react-redux';
import {Key, Button} from 'akeneo-design-system';
import {attributeCreationStart} from 'akeneoassetmanager/domain/event/attribute/create';
import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import {CreateState} from 'akeneoassetmanager/application/reducer/attribute/create';
import CreateAttributeModal from 'akeneoassetmanager/application/component/attribute/create';
import ManageOptionsView from 'akeneoassetmanager/application/component/attribute/edit/option';
import AttributeIdentifier from 'akeneoassetmanager/domain/model/attribute/identifier';
import {AssetFamily, getAssetFamilyLabel} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {attributeEditionStartByIdentifier} from 'akeneoassetmanager/application/action/attribute/edit';
import AttributeEditForm from 'akeneoassetmanager/application/component/attribute/edit';
import Header from 'akeneoassetmanager/application/component/asset-family/edit/header';
import {AssetFamilyBreadcrumb} from 'akeneoassetmanager/application/component/app/breadcrumb';
import denormalizeAttribute from 'akeneoassetmanager/application/denormalizer/attribute/attribute';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {getAttributeIcon} from 'akeneoassetmanager/application/configuration/attribute';
import ErrorBoundary from 'akeneoassetmanager/application/component/app/error-boundary';
import {EditOptionState} from 'akeneoassetmanager/application/reducer/attribute/type/option';
import {canEditAssetFamily, canEditLocale} from 'akeneoassetmanager/application/reducer/right';
import {StickyHeader} from 'akeneoassetmanager/application/component/asset-family/edit/permission';
import {useTranslate, Translate} from '@akeneo-pim-community/legacy-bridge';

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

const renderSystemAttribute = (type: string, identifier: string, translate: Translate) => {
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
          value={translate(`pim_asset_manager.attribute.default.${identifier}`)}
          readOnly
        />
      </div>
    </div>
  );
};

const renderSystemAttributes = (translate: Translate) => {
  return <>{renderSystemAttribute('text', 'code', translate)}</>;
};

const AttributePlaceholders = () => {
  const translate = useTranslate();

  return (
    <>
      {Array(8)
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
                  {translate(`pim_asset_manager.attribute.type.text`)}{' '}
                  {`(${translate('pim_asset_manager.attribute.is_required')})`}
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
        ))}
    </>
  );
};

interface AttributeViewProps {
  normalizedAttribute: NormalizedAttribute;
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

const AttributeView = memo(({normalizedAttribute, onAttributeEdit, locale, rights}: AttributeViewProps) => {
  const translate = useTranslate();
  const attribute = denormalizeAttribute(normalizedAttribute);
  const icon = getAttributeIcon(attribute.getType());

  return (
    <div
      className="AknFieldContainer"
      data-placeholder="false"
      data-identifier={attribute.getCode()}
      data-type={attribute.getType()}
    >
      <div className="AknFieldContainer-header AknFieldContainer-header--light">
        <label
          className="AknFieldContainer-label AknFieldContainer-label--withImage"
          htmlFor={`pim_asset_manager.asset_family.properties.${attribute.getCode()}`}
        >
          <img className="AknFieldContainer-labelImage" src={icon} />
          <span>
            {attribute.getCode()}{' '}
            {attribute.isRequired ? `(${translate('pim_asset_manager.attribute.is_required')})` : ''}
          </span>
        </label>
      </div>
      <div className="AknFieldContainer-inputContainer">
        <input
          type="text"
          autoComplete="off"
          id={`pim_asset_manager.asset_family.properties.${attribute.getCode()}`}
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
});

const AttributesView = ({
  assetFamily,
  rights,
  events,
  context,
  attributes,
  firstLoading,
  createAttribute,
  editAttribute,
  options,
}: CreateProps) => {
  const translate = useTranslate();

  const assetFamilyLabel = getAssetFamilyLabel(assetFamily, context.locale);

  return (
    <>
      <Header
        label={translate('pim_asset_manager.asset_family.tab.attribute')}
        image={assetFamily.image}
        primaryAction={(defaultFocus: React.RefObject<any>) =>
          rights.attribute.create ? (
            <Button level="secondary" onClick={events.onAttributeCreationStart} ref={defaultFocus} tabIndex={0}>
              {translate('pim_asset_manager.attribute.button.add')}
            </Button>
          ) : null
        }
        secondaryActions={() => null}
        withLocaleSwitcher={true}
        withChannelSwitcher={false}
        isDirty={false}
        breadcrumb={<AssetFamilyBreadcrumb assetFamilyLabel={assetFamilyLabel} />}
        displayActions={rights.attribute.create}
      />
      <div className="AknSubsection">
        <StickyHeader>
          <span className="group-label">{translate('pim_asset_manager.asset_family.attribute.title')}</span>
        </StickyHeader>
        {firstLoading || 0 < attributes.length ? (
          <div className="AknSubsection-container">
            <div className="AknFormContainer AknFormContainer--withPadding">
              {renderSystemAttributes(translate)}
              {firstLoading ? (
                <AttributePlaceholders />
              ) : (
                <>
                  {attributes.map((attribute: NormalizedAttribute) => (
                    <ErrorBoundary
                      key={attribute.identifier}
                      errorMessage={translate('pim_asset_manager.asset_family.attribute.error.render_list')}
                    >
                      <AttributeView
                        normalizedAttribute={attribute}
                        onAttributeEdit={events.onAttributeEdit}
                        locale={context.locale}
                        rights={rights}
                      />
                    </ErrorBoundary>
                  ))}
                </>
              )}
            </div>
            {editAttribute && <AttributeEditForm rights={rights} />}
          </div>
        ) : (
          <>
            <div className="AknSubsection-container">
              <div className="AknFormContainer AknFormContainer--withPadding">{renderSystemAttributes(translate)}</div>
            </div>
            <div className="AknGridContainer-noData AknGridContainer-noData--small">
              <div className="AknGridContainer-noDataTitle">
                {translate('pim_asset_manager.attribute.no_data.title', {entityLabel: assetFamilyLabel})}
              </div>
              <div className="AknGridContainer-noDataSubtitle">
                {translate('pim_asset_manager.attribute.no_data.subtitle')}
              </div>
              <button className="AknButton AknButton--action" onClick={events.onAttributeCreationStart}>
                {translate('pim_asset_manager.attribute.button.add')}
              </button>
            </div>
          </>
        )}
        {createAttribute.active && <CreateAttributeModal />}
        {options.isActive && <ManageOptionsView rights={rights} />}
      </div>
    </>
  );
};

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
      assetFamily: state.form.data,
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
