import React, {useRef} from 'react';
import {connect, useDispatch} from 'react-redux';
import styled, {FlattenSimpleInterpolation} from 'styled-components';
import {DeleteIcon, Key, Checkbox, Button, SectionTitle, useAutoFocus, useBooleanState} from 'akeneo-design-system';
import {
  DeleteModal,
  getErrorsForPath,
  TextField,
  useSecurity,
  useTranslate,
  ValidationError,
} from '@akeneo-pim-community/shared';
import {getErrorsView} from 'akeneoassetmanager/application/component/app/validation-error';
import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import {
  attributeEditionAdditionalPropertyUpdated,
  attributeEditionCancel,
  attributeEditionIsRequiredUpdated,
  attributeEditionLabelUpdated,
  attributeEditionIsReadOnlyUpdated,
} from 'akeneoassetmanager/domain/event/attribute/edit';
import {saveAttribute} from 'akeneoassetmanager/application/action/attribute/edit';
import {TextAttribute} from 'akeneoassetmanager/domain/model/attribute/type/text';
import {deleteAttribute} from 'akeneoassetmanager/application/action/attribute/delete';
import {attributeidentifiersAreEqual} from 'akeneoassetmanager/domain/model/attribute/identifier';
import {Attribute, NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import ErrorBoundary from 'akeneoassetmanager/application/component/app/error-boundary';
import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {ButtonContainer} from 'akeneoassetmanager/application/component/app/button';
import {useAttributeView} from 'akeneoassetmanager/application/hooks/attribute/useAttributeView';
import {useAttributeDenormalizer} from 'akeneoassetmanager/application/hooks/attribute/useAttributeDenormalizer';
import {useAttributeFetcher} from 'akeneoassetmanager/infrastructure/fetcher/useAttributeFetcher';

const DeleteButton = styled.span`
  flex: 1;

  :hover {
    ${DeleteIcon.animatedMixin as FlattenSimpleInterpolation}
  }
`;

const SpacedTitle = styled(SectionTitle)`
  margin: 0 20px;
  width: auto;
`;

const Fields = styled.div`
  margin: 20px;
  display: flex;
  gap: 14px;
  flex-direction: column;
`;

interface OwnProps {
  rights: {
    locale: {
      edit: boolean;
    };
    assetFamily: {
      edit: boolean;
    };
  };
}

interface StateProps extends OwnProps {
  context: {
    locale: string;
  };
  assetFamily: AssetFamily;
  isSaving: boolean;
  isActive: boolean;
  normalizedAttribute: NormalizedAttribute;
  errors: ValidationError[];
}

interface DispatchProps {
  events: {
    onLabelUpdated: (value: string, locale: string) => void;
    onIsRequiredUpdated: (isRequired: boolean) => void;
    onIsReadOnlyUpdated: (isReadOnly: boolean) => void;
    onAdditionalPropertyUpdated: (property: string, value: any) => void;
    onCancel: () => void;
    onSubmit: () => void;
  };
}

interface EditProps extends StateProps, DispatchProps {}

const AdditionalProperty = ({
  attribute,
  onAdditionalPropertyUpdated,
  errors,
  locale,
  rights,
}: {
  attribute: Attribute;
  onAdditionalPropertyUpdated: (property: string, value: any) => void;
  errors: ValidationError[];
  locale: string;
  rights: {
    locale: {
      edit: boolean;
    };
    attribute: {
      edit: boolean;
    };
  };
}): JSX.Element => {
  const AttributeView = useAttributeView(attribute);
  const dispatch = useDispatch();
  const attributeFetcher = useAttributeFetcher();
  const attributeDenormalizer = useAttributeDenormalizer();

  return (
    <AttributeView
      attribute={attribute as TextAttribute}
      onAdditionalPropertyUpdated={onAdditionalPropertyUpdated}
      onSubmit={() => dispatch(saveAttribute(attributeFetcher, attributeDenormalizer))}
      errors={errors}
      locale={locale}
      rights={rights}
    />
  );
};

const Edit = ({isActive, isSaving, rights, assetFamily, normalizedAttribute, context, events, errors}: EditProps) => {
  const translate = useTranslate();
  const {isGranted} = useSecurity();
  const labelInputRef = useRef<HTMLInputElement>(null);
  const [isDeleteModalOpen, openDeleteModal, closeDeleteModal] = useBooleanState();
  const attributeDenormalizer = useAttributeDenormalizer();
  const attributeFetcher = useAttributeFetcher();
  const attribute = attributeDenormalizer(normalizedAttribute);
  const dispatch = useDispatch();

  const label = attribute.getLabel(context.locale);
  const canEditAttribute = isGranted('akeneo_assetmanager_attribute_edit') && rights.assetFamily.edit;
  const canDeleteAttribute = canEditAttribute && isGranted('akeneo_assetmanager_attribute_delete');
  const canEditLabel = canEditAttribute && rights.locale.edit;

  const handleLabelChange = (value: string) => events.onLabelUpdated(value, context.locale);

  // This will be simplified in the near future
  const displayDeleteButton =
    canDeleteAttribute &&
    !attributeidentifiersAreEqual(assetFamily.attributeAsLabel, attribute.getIdentifier()) &&
    !attributeidentifiersAreEqual(assetFamily.attributeAsMainMedia, attribute.getIdentifier());

  useAutoFocus(labelInputRef);

  return (
    <>
      <div className={`AknQuickEdit ${!isActive ? 'AknQuickEdit--hidden' : ''}`}>
        <div className={`AknLoadingMask ${!isSaving ? 'AknLoadingMask--hidden' : ''}`} />
        <SpacedTitle>
          <SectionTitle.Title>
            {translate('pim_asset_manager.attribute.edit.title', {code: attribute.getCode()})}
          </SectionTitle.Title>
        </SpacedTitle>
        <Fields>
          <TextField
            label={translate('pim_asset_manager.attribute.edit.input.label')}
            locale={context.locale}
            ref={labelInputRef}
            value={attribute.getLabel(context.locale, false)}
            onChange={handleLabelChange}
            readOnly={!canEditLabel}
            errors={getErrorsForPath(errors, 'labels')}
          />
          <div>
            <Checkbox checked={attribute.valuePerChannel} readOnly={true}>
              {translate('pim_asset_manager.attribute.edit.input.value_per_channel')}
            </Checkbox>
            {getErrorsView(errors, 'valuePerChannel')}
          </div>
          <div>
            <Checkbox checked={attribute.valuePerLocale} readOnly={true}>
              {translate('pim_asset_manager.attribute.edit.input.value_per_locale')}
            </Checkbox>
            {getErrorsView(errors, 'valuePerLocale')}
          </div>
          <div>
            <Checkbox checked={attribute.isRequired} onChange={events.onIsRequiredUpdated} readOnly={!canEditAttribute}>
              {translate('pim_asset_manager.attribute.edit.input.is_required')}
            </Checkbox>
            {getErrorsView(errors, 'isRequired')}
          </div>
          <div>
            <Checkbox checked={attribute.isReadOnly} onChange={events.onIsReadOnlyUpdated} readOnly={!canEditAttribute}>
              {translate('pim_asset_manager.attribute.edit.input.is_read_only')}
            </Checkbox>
            {getErrorsView(errors, 'isReadOnly')}
          </div>
          <ErrorBoundary errorMessage={translate('pim_asset_manager.asset_family.attribute.error.render_edit')}>
            <AdditionalProperty
              attribute={attribute}
              onAdditionalPropertyUpdated={events.onAdditionalPropertyUpdated}
              errors={errors}
              locale={context.locale}
              rights={{
                locale: {
                  edit: rights.locale.edit,
                },
                attribute: {
                  edit: canEditAttribute,
                },
              }}
            />
          </ErrorBoundary>
        </Fields>
        <footer className="AknSubsection-footer AknSubsection-footer--sticky">
          {displayDeleteButton ? (
            <DeleteButton
              className="AknButton AknButton--delete"
              tabIndex={0}
              onKeyPress={(event: React.KeyboardEvent<HTMLSpanElement>) => {
                if (Key.Space === event.key) openDeleteModal();
              }}
              onClick={openDeleteModal}
            >
              <DeleteIcon className="AknButton-animatedIcon" />
              {translate('pim_asset_manager.attribute.edit.delete')}
            </DeleteButton>
          ) : (
            <span style={{flex: 1}} />
          )}
          <ButtonContainer>
            <Button onClick={events.onCancel} level="tertiary">
              {translate('pim_asset_manager.attribute.edit.cancel')}
            </Button>
            {canEditAttribute && (
              <Button onClick={() => dispatch(saveAttribute(attributeFetcher, attributeDenormalizer))}>
                {translate('pim_asset_manager.attribute.edit.save')}
              </Button>
            )}
          </ButtonContainer>
        </footer>
      </div>
      {isDeleteModalOpen && (
        <DeleteModal
          title={translate('pim_asset_manager.attribute.delete.title')}
          onConfirm={() => dispatch(deleteAttribute(attributeFetcher, attribute.getIdentifier()))}
          onCancel={closeDeleteModal}
        >
          {translate('pim_asset_manager.attribute.delete.message', {attributeLabel: label})}
        </DeleteModal>
      )}
    </>
  );
};

export default connect(
  (state: EditState, ownProps: OwnProps): StateProps => {
    return {
      ...ownProps,
      isActive: state.attribute.isActive,
      normalizedAttribute: state.attribute.data,
      errors: state.attribute.errors,
      assetFamily: state.form.data,
      isSaving: state.attribute.isSaving,
      context: {
        locale: state.user.catalogLocale,
      },
    } as StateProps;
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        onLabelUpdated: (value: string, locale: string) => {
          dispatch(attributeEditionLabelUpdated(value, locale));
        },
        onIsRequiredUpdated: (isRequired: boolean) => {
          dispatch(attributeEditionIsRequiredUpdated(isRequired));
        },
        onIsReadOnlyUpdated: (isReadOnly: boolean) => {
          dispatch(attributeEditionIsReadOnlyUpdated(isReadOnly));
        },
        onAdditionalPropertyUpdated: (property: string, value: any) => {
          dispatch(attributeEditionAdditionalPropertyUpdated(property, value));
        },
        onCancel: () => {
          dispatch(attributeEditionCancel());
        },
      },
    } as DispatchProps;
  }
)(Edit);
