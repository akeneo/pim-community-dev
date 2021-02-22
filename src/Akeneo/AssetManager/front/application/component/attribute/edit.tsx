import React from 'react';
import {connect} from 'react-redux';
import styled, {FlattenSimpleInterpolation} from 'styled-components';
import {DeleteIcon, Key, Checkbox, Button, SectionTitle} from 'akeneo-design-system';
import __ from 'akeneoassetmanager/tools/translator';
import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import Flag from 'akeneoassetmanager/tools/component/flag';
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
import {createLocaleFromCode} from 'akeneoassetmanager/domain/model/locale';
import {TextAttribute} from 'akeneoassetmanager/domain/model/attribute/type/text';
import {deleteAttribute} from 'akeneoassetmanager/application/action/attribute/delete';
import AttributeIdentifier, {attributeidentifiersAreEqual} from 'akeneoassetmanager/domain/model/attribute/identifier';
import DeleteModal from 'akeneoassetmanager/application/component/app/delete-modal';
import denormalizeAttribute from 'akeneoassetmanager/application/denormalizer/attribute/attribute';
import {Attribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {getAttributeView} from 'akeneoassetmanager/application/configuration/attribute';
import ErrorBoundary from 'akeneoassetmanager/application/component/app/error-boundary';
import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {ButtonContainer} from '../app/button';

const DeleteButton = styled.span`
  flex: 1;

  :hover {
    ${DeleteIcon.animatedMixin as FlattenSimpleInterpolation}
  }
`;

const SpacedTitle = styled(SectionTitle)`
  margin: 0 20px;
`;

const Fields = styled.div`
  margin: 0 20px 20px;
  display: flex;
  gap: 14px;
  flex-direction: column;
`;

interface OwnProps {
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

interface StateProps extends OwnProps {
  context: {
    locale: string;
  };
  assetFamily: AssetFamily;
  isSaving: boolean;
  isActive: boolean;
  attribute: Attribute;
  errors: ValidationError[];
}

interface DispatchProps {
  events: {
    onLabelUpdated: (value: string, locale: string) => void;
    onIsRequiredUpdated: (isRequired: boolean) => void;
    onIsReadOnlyUpdated: (isReadOnly: boolean) => void;
    onAdditionalPropertyUpdated: (property: string, value: any) => void;
    onAttributeDelete: (attributeIdentifier: AttributeIdentifier) => void;
    onCancel: () => void;
    onSubmit: () => void;
  };
}

interface EditProps extends StateProps, DispatchProps {}

const getAdditionalProperty = (
  attribute: Attribute,
  onAdditionalPropertyUpdated: (property: string, value: any) => void,
  onSubmit: () => void,
  errors: ValidationError[],
  locale: string,
  rights: {
    locale: {
      edit: boolean;
    };
    attribute: {
      create: boolean;
      edit: boolean;
      delete: boolean;
    };
  }
): JSX.Element => {
  const AttributeView = getAttributeView(attribute);

  return (
    <AttributeView
      attribute={attribute as TextAttribute}
      onAdditionalPropertyUpdated={onAdditionalPropertyUpdated}
      onSubmit={onSubmit}
      errors={errors}
      locale={locale}
      rights={rights}
    />
  );
};

class Edit extends React.Component<EditProps> {
  private labelInput: HTMLInputElement;
  public props: EditProps;
  public state: {previousAttribute: string | null; currentAttribute: string | null; isDeleteModalOpen: boolean} = {
    previousAttribute: null,
    currentAttribute: null,
    isDeleteModalOpen: false,
  };

  componentDidMount() {
    if (this.labelInput) {
      this.labelInput.focus();
    }
  }

  componentDidUpdate(prevProps: EditProps) {
    if (this.labelInput && this.state.currentAttribute !== this.state.previousAttribute) {
      this.labelInput.focus();
    }

    const quickEdit = this.refs.quickEdit as any;
    if (null !== quickEdit && !this.props.isActive && prevProps.isActive) {
      setTimeout(() => {
        quickEdit.style.display = 'none';
      }, 500);
    } else {
      quickEdit.style.display = 'block';
    }
  }

  static getDerivedStateFromProps(newProps: EditProps, state: {previousAttribute: string; currentAttribute: string}) {
    return {previousAttribute: state.currentAttribute, currentAttribute: newProps.attribute.identifier.normalize()};
  }

  private onLabelUpdate = (event: React.FormEvent<HTMLInputElement>) => {
    this.props.events.onLabelUpdated(event.currentTarget.value, this.props.context.locale);
  };

  private onKeyPress = (event: React.KeyboardEvent<HTMLInputElement>) => {
    if (Key.Enter === event.key) this.props.events.onSubmit();
  };

  //TODO Use DSM Fields
  render(): JSX.Element | JSX.Element[] | null {
    const label = this.props.attribute.getLabel(this.props.context.locale);
    const canEditLabel = this.props.rights.attribute.edit && this.props.rights.locale.edit;
    const labelClassName = `AknTextField AknTextField--light ${!canEditLabel ? 'AknTextField--disabled' : ''}`;

    // This will be simplyfied in the near future
    const displayDeleteButton =
      this.props.rights.attribute.delete &&
      !attributeidentifiersAreEqual(this.props.assetFamily.attributeAsLabel, this.props.attribute.getIdentifier()) &&
      !attributeidentifiersAreEqual(this.props.assetFamily.attributeAsMainMedia, this.props.attribute.getIdentifier());

    return (
      <>
        <div className={`AknQuickEdit ${!this.props.isActive ? 'AknQuickEdit--hidden' : ''}`} ref="quickEdit">
          <div className={`AknLoadingMask ${!this.props.isSaving ? 'AknLoadingMask--hidden' : ''}`} />
          <div className="AknSubsection">
            <SpacedTitle>
              <SectionTitle.Title>
                {__('pim_asset_manager.attribute.edit.title', {code: this.props.attribute.getCode()})}
              </SectionTitle.Title>
            </SpacedTitle>
            <Fields>
              <div className="AknFieldContainer--packed" data-code="label">
                <div className="AknFieldContainer-header AknFieldContainer-header--light">
                  <label className="AknFieldContainer-label" htmlFor="pim_asset_manager.attribute.edit.input.label">
                    {__('pim_asset_manager.attribute.edit.input.label')}
                  </label>
                </div>
                <div className="AknFieldContainer-inputContainer">
                  <input
                    type="text"
                    autoComplete="off"
                    ref={(input: HTMLInputElement) => {
                      this.labelInput = input;
                    }}
                    className={labelClassName}
                    id="pim_asset_manager.attribute.edit.input.label"
                    name="label"
                    value={this.props.attribute.getLabel(this.props.context.locale, false)}
                    onChange={this.onLabelUpdate}
                    onKeyPress={this.onKeyPress}
                    readOnly={!canEditLabel}
                  />
                  <Flag
                    locale={createLocaleFromCode(this.props.context.locale)}
                    displayLanguage={false}
                    className="AknFieldContainer-inputSides"
                  />
                </div>
                {getErrorsView(this.props.errors, 'labels')}
              </div>
              <div data-code="valuePerChannel">
                <Checkbox
                  id="pim_asset_manager.attribute.edit.input.value_per_channel"
                  checked={this.props.attribute.valuePerChannel}
                  readOnly={true}
                >
                  {__('pim_asset_manager.attribute.edit.input.value_per_channel')}
                </Checkbox>
                {getErrorsView(this.props.errors, 'valuePerChannel')}
              </div>
              <div data-code="valuePerLocale">
                <Checkbox
                  id="pim_asset_manager.attribute.edit.input.value_per_locale"
                  checked={this.props.attribute.valuePerLocale}
                  readOnly={true}
                >
                  {__('pim_asset_manager.attribute.edit.input.value_per_locale')}
                </Checkbox>
                {getErrorsView(this.props.errors, 'valuePerLocale')}
              </div>
              <div data-code="isRequired">
                <Checkbox
                  id="pim_asset_manager.attribute.edit.input.is_required"
                  checked={this.props.attribute.isRequired}
                  onChange={this.props.events.onIsRequiredUpdated}
                  readOnly={!this.props.rights.attribute.edit}
                >
                  {__('pim_asset_manager.attribute.edit.input.is_required')}
                </Checkbox>
                {getErrorsView(this.props.errors, 'isRequired')}
              </div>
              <div data-code="isReadOnly">
                <Checkbox
                  id="pim_asset_manager.attribute.edit.input.is_read_only"
                  checked={this.props.attribute.isReadOnly}
                  onChange={this.props.events.onIsReadOnlyUpdated}
                  readOnly={!this.props.rights.attribute.edit}
                >
                  {__('pim_asset_manager.attribute.edit.input.is_read_only')}
                </Checkbox>
                {getErrorsView(this.props.errors, 'isReadOnly')}
              </div>
              <ErrorBoundary errorMessage={__('pim_asset_manager.asset_family.attribute.error.render_edit')}>
                {getAdditionalProperty(
                  this.props.attribute,
                  this.props.events.onAdditionalPropertyUpdated,
                  this.props.events.onSubmit,
                  this.props.errors,
                  this.props.context.locale,
                  this.props.rights
                )}
              </ErrorBoundary>
            </Fields>
            <footer className="AknSubsection-footer AknSubsection-footer--sticky">
              {displayDeleteButton ? (
                <DeleteButton
                  className="AknButton AknButton--delete"
                  tabIndex={0}
                  onKeyPress={(event: React.KeyboardEvent<HTMLDivElement>) => {
                    if (Key.Space === event.key) this.setState({isDeleteModalOpen: true});
                  }}
                  onClick={() => this.setState({isDeleteModalOpen: true})}
                >
                  <DeleteIcon className="AknButton-animatedIcon" />
                  {__('pim_asset_manager.attribute.edit.delete')}
                </DeleteButton>
              ) : (
                <span style={{flex: 1}} />
              )}
              <ButtonContainer>
                <Button onClick={this.props.events.onCancel} level="tertiary">
                  {__('pim_asset_manager.attribute.edit.cancel')}
                </Button>
                {this.props.rights.attribute.edit && (
                  <Button onClick={this.props.events.onSubmit}>{__('pim_asset_manager.attribute.edit.save')}</Button>
                )}
              </ButtonContainer>
            </footer>
          </div>
        </div>
        {this.state.isDeleteModalOpen && (
          <DeleteModal
            message={__('pim_asset_manager.attribute.delete.message', {attributeLabel: label})}
            title={__('pim_asset_manager.attribute.delete.title')}
            onConfirm={() => this.props.events.onAttributeDelete(this.props.attribute.getIdentifier())}
            onCancel={() => this.setState({isDeleteModalOpen: false})}
          />
        )}
      </>
    );
  }
}

export default connect(
  (state: EditState, ownProps: OwnProps): StateProps => {
    return {
      ...ownProps,
      isActive: state.attribute.isActive,
      attribute: denormalizeAttribute(state.attribute.data),
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
        onSubmit: () => {
          dispatch(saveAttribute());
        },
        onAttributeDelete: (attributeIdentifier: AttributeIdentifier) => {
          dispatch(deleteAttribute(attributeIdentifier));
        },
      },
    } as DispatchProps;
  }
)(Edit);
