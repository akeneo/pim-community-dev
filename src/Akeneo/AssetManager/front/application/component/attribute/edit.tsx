import React from 'react';
import {connect} from 'react-redux';
import styled, {FlattenSimpleInterpolation} from 'styled-components';
import {DeleteIcon, Key} from 'akeneo-design-system';
import __ from 'akeneoassetmanager/tools/translator';
import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import Flag from 'akeneoassetmanager/tools/component/flag';
import {getErrorsView} from 'akeneoassetmanager/application/component/app/validation-error';
import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import Checkbox from 'akeneoassetmanager/application/component/app/checkbox';
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

const DeleteButton = styled.span`
  flex: 1;

  :hover {
    ${DeleteIcon.animatedMixin as FlattenSimpleInterpolation}
  }
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
      <React.Fragment>
        <div className={`AknQuickEdit ${!this.props.isActive ? 'AknQuickEdit--hidden' : ''}`} ref="quickEdit">
          <div className={`AknLoadingMask ${!this.props.isSaving ? 'AknLoadingMask--hidden' : ''}`} />
          <div className="AknSubsection">
            <header
              style={{margin: '0 20px 25px 20px'}}
              className="AknSubsection-title AknSubsection-title--sticky AknSubsection-title--light"
            >
              {__('pim_asset_manager.attribute.edit.title', {code: this.props.attribute.getCode()})}
            </header>
            <div className="AknFormContainer AknFormContainer--expanded AknFormContainer--withSmallPadding">
              <div className="AknFieldContainer" data-code="label">
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
              <div className="AknFieldContainer AknFieldContainer--packed" data-code="valuePerChannel">
                <div className="AknFieldContainer-header">
                  <label
                    className="AknFieldContainer-label"
                    htmlFor="pim_asset_manager.attribute.edit.input.value_per_channel"
                  >
                    <Checkbox
                      id="pim_asset_manager.attribute.edit.input.value_per_channel"
                      value={this.props.attribute.valuePerChannel}
                      readOnly
                    />
                    {__('pim_asset_manager.attribute.edit.input.value_per_channel')}
                  </label>
                </div>
                {getErrorsView(this.props.errors, 'valuePerChannel')}
              </div>
              <div className="AknFieldContainer AknFieldContainer--packed" data-code="valuePerLocale">
                <div className="AknFieldContainer-header">
                  <label
                    className="AknFieldContainer-label"
                    htmlFor="pim_asset_manager.attribute.edit.input.value_per_locale"
                  >
                    <Checkbox
                      id="pim_asset_manager.attribute.edit.input.value_per_locale"
                      value={this.props.attribute.valuePerLocale}
                      readOnly
                    />
                    {__('pim_asset_manager.attribute.edit.input.value_per_locale')}
                  </label>
                </div>
                {getErrorsView(this.props.errors, 'valuePerLocale')}
              </div>
              <div className="AknFieldContainer AknFieldContainer--packed" data-code="isRequired">
                <div className="AknFieldContainer-header">
                  <label
                    className="AknFieldContainer-label AknFieldContainer-label--inline"
                    htmlFor="pim_asset_manager.attribute.edit.input.is_required"
                  >
                    <Checkbox
                      id="pim_asset_manager.attribute.edit.input.is_required"
                      value={this.props.attribute.isRequired}
                      onChange={this.props.events.onIsRequiredUpdated}
                      readOnly={!this.props.rights.attribute.edit}
                    />
                    <span
                      onClick={() => {
                        if (this.props.rights.attribute.edit) {
                          this.props.events.onIsRequiredUpdated(!this.props.attribute.isRequired);
                        }
                      }}
                    >
                      {__('pim_asset_manager.attribute.edit.input.is_required')}
                    </span>
                  </label>
                </div>
                {getErrorsView(this.props.errors, 'isRequired')}
              </div>

              <div className="AknFieldContainer AknFieldContainer--packed" data-code="isReadOnly">
                <div className="AknFieldContainer-header">
                  <label
                    className="AknFieldContainer-label AknFieldContainer-label--inline"
                    htmlFor="pim_asset_manager.attribute.edit.input.is_read_only"
                  >
                    <Checkbox
                      id="pim_asset_manager.attribute.edit.input.is_read_only"
                      value={this.props.attribute.isReadOnly}
                      onChange={this.props.events.onIsReadOnlyUpdated}
                      readOnly={!this.props.rights.attribute.edit}
                    />
                    <span
                      onClick={() => {
                        if (this.props.rights.attribute.edit) {
                          this.props.events.onIsReadOnlyUpdated(!this.props.attribute.isReadOnly);
                        }
                      }}
                    >
                      {__('pim_asset_manager.attribute.edit.input.is_read_only')}
                    </span>
                  </label>
                </div>
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
            </div>
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
              <span
                title={__('pim_asset_manager.attribute.edit.cancel')}
                className="AknButton AknButton--small AknButton--grey AknButton--spaced"
                tabIndex={0}
                onClick={this.props.events.onCancel}
                onKeyPress={(event: React.KeyboardEvent<HTMLElement>) => {
                  if (Key.Space === event.key) this.props.events.onCancel();
                }}
              >
                {__('pim_asset_manager.attribute.edit.cancel')}
              </span>
              {this.props.rights.attribute.edit ? (
                <span
                  title={__('pim_asset_manager.attribute.edit.save')}
                  className="AknButton AknButton--small AknButton--apply AknButton--spaced"
                  tabIndex={0}
                  onClick={this.props.events.onSubmit}
                  onKeyPress={(event: React.KeyboardEvent<HTMLElement>) => {
                    if (Key.Space === event.key) this.props.events.onSubmit();
                  }}
                >
                  {__('pim_asset_manager.attribute.edit.save')}
                </span>
              ) : null}
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
      </React.Fragment>
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
