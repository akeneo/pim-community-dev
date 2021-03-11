import React from 'react';
import {connect} from 'react-redux';
import styled, {FlattenSimpleInterpolation} from 'styled-components';
import {DeleteIcon, Key, Checkbox, SectionTitle} from 'akeneo-design-system';
import __ from 'akeneoreferenceentity/tools/translator';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import Flag from 'akeneoreferenceentity/tools/component/flag';
import {getErrorsView} from 'akeneoreferenceentity/application/component/app/validation-error';
import {EditState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import {
  attributeEditionAdditionalPropertyUpdated,
  attributeEditionCancel,
  attributeEditionIsRequiredUpdated,
  attributeEditionLabelUpdated,
} from 'akeneoreferenceentity/domain/event/attribute/edit';
import {saveAttribute} from 'akeneoreferenceentity/application/action/attribute/edit';
import {createLocaleFromCode} from 'akeneoreferenceentity/domain/model/locale';
import {TextAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/text';
import {deleteAttribute} from 'akeneoreferenceentity/application/action/attribute/delete';
import AttributeIdentifier from 'akeneoreferenceentity/domain/model/attribute/identifier';
import DeleteModal from 'akeneoreferenceentity/application/component/app/delete-modal';
import {cancelDeleteModal, openDeleteModal} from 'akeneoreferenceentity/application/event/confirmDelete';
import denormalizeAttribute from 'akeneoreferenceentity/application/denormalizer/attribute/attribute';
import {Attribute} from 'akeneoreferenceentity/domain/model/attribute/attribute';
import {getAttributeView} from 'akeneoreferenceentity/application/configuration/attribute';
import ErrorBoundary from 'akeneoreferenceentity/application/component/app/error-boundary';
import ReferenceEntity, {
  denormalizeReferenceEntity,
} from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';

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
  referenceEntity: ReferenceEntity;
  isSaving: boolean;
  isActive: boolean;
  attribute: Attribute;
  errors: ValidationError[];
  confirmDelete: {
    isActive: boolean;
  };
}

interface DispatchProps {
  events: {
    onLabelUpdated: (value: string, locale: string) => void;
    onIsRequiredUpdated: (isRequired: boolean) => void;
    onAdditionalPropertyUpdated: (property: string, value: any) => void;
    onAttributeDelete: (attributeIdentifier: AttributeIdentifier) => void;
    onOpenDeleteModal: () => void;
    onCancelDeleteModal: () => void;
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
  public state: {previousAttribute: string | null; currentAttribute: string | null} = {
    previousAttribute: null,
    currentAttribute: null,
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
      !this.props.referenceEntity.getAttributeAsLabel().equals(this.props.attribute.getIdentifier()) &&
      !this.props.referenceEntity.getAttributeAsImage().equals(this.props.attribute.getIdentifier());

    return (
      <>
        <div className={`AknQuickEdit ${!this.props.isActive ? 'AknQuickEdit--hidden' : ''}`} ref="quickEdit">
          <div className={`AknLoadingMask ${!this.props.isSaving ? 'AknLoadingMask--hidden' : ''}`} />
          <div className="AknSubsection">
            <SpacedTitle>
              <SectionTitle.Title>
                {__('pim_reference_entity.attribute.edit.title', {code: this.props.attribute.getCode().stringValue()})}
              </SectionTitle.Title>
            </SpacedTitle>
            <Fields>
              <div className="AknFieldContainer--packed" data-code="label">
                <div className="AknFieldContainer-header AknFieldContainer-header--light">
                  <label className="AknFieldContainer-label" htmlFor="pim_reference_entity.attribute.edit.input.label">
                    {__('pim_reference_entity.attribute.edit.input.label')}
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
                    id="pim_reference_entity.attribute.edit.input.label"
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
                  id="pim_reference_entity.attribute.edit.input.value_per_channel"
                  checked={this.props.attribute.valuePerChannel}
                  readOnly={true}
                >
                  {__('pim_reference_entity.attribute.edit.input.value_per_channel')}
                </Checkbox>
                {getErrorsView(this.props.errors, 'valuePerChannel')}
              </div>
              <div data-code="valuePerLocale">
                <Checkbox
                  id="pim_reference_entity.attribute.edit.input.value_per_locale"
                  checked={this.props.attribute.valuePerLocale}
                  readOnly={true}
                >
                  {__('pim_reference_entity.attribute.edit.input.value_per_locale')}
                </Checkbox>
                {getErrorsView(this.props.errors, 'valuePerLocale')}
              </div>
              <div data-code="isRequired">
                <Checkbox
                  id="pim_reference_entity.attribute.edit.input.is_required"
                  checked={this.props.attribute.isRequired}
                  onChange={this.props.events.onIsRequiredUpdated}
                  readOnly={!this.props.rights.attribute.edit}
                >
                  {__('pim_reference_entity.attribute.edit.input.is_required')}
                </Checkbox>
                {getErrorsView(this.props.errors, 'isRequired')}
              </div>
              <ErrorBoundary errorMessage={__('pim_reference_entity.reference_entity.attribute.error.render_edit')}>
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
                    if (Key.Space === event.key) this.props.events.onOpenDeleteModal();
                  }}
                  onClick={() => this.props.events.onOpenDeleteModal()}
                >
                  <DeleteIcon className="AknButton-animatedIcon" />
                  {__('pim_reference_entity.attribute.edit.delete')}
                </DeleteButton>
              ) : (
                <span style={{flex: 1}} />
              )}
              <span
                title={__('pim_reference_entity.attribute.edit.cancel')}
                className="AknButton AknButton--small AknButton--grey AknButton--spaced"
                tabIndex={0}
                onClick={this.props.events.onCancel}
                onKeyPress={(event: React.KeyboardEvent<HTMLElement>) => {
                  if (Key.Space === event.key) this.props.events.onCancel();
                }}
              >
                {__('pim_reference_entity.attribute.edit.cancel')}
              </span>
              {this.props.rights.attribute.edit ? (
                <span
                  title={__('pim_reference_entity.attribute.edit.save')}
                  className="AknButton AknButton--small AknButton--apply AknButton--spaced"
                  tabIndex={0}
                  onClick={this.props.events.onSubmit}
                  onKeyPress={(event: React.KeyboardEvent<HTMLElement>) => {
                    if (Key.Space === event.key) this.props.events.onSubmit();
                  }}
                >
                  {__('pim_reference_entity.attribute.edit.save')}
                </span>
              ) : null}
            </footer>
          </div>
        </div>
        {this.props.confirmDelete.isActive && (
          <DeleteModal
            message={__('pim_reference_entity.attribute.delete.message', {attributeLabel: label})}
            title={__('pim_reference_entity.attribute.delete.title')}
            onConfirm={() => {
              this.props.events.onAttributeDelete(this.props.attribute.getIdentifier());
            }}
            onCancel={this.props.events.onCancelDeleteModal}
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
      referenceEntity: denormalizeReferenceEntity(state.form.data),
      isSaving: state.attribute.isSaving,
      context: {
        locale: state.user.catalogLocale,
      },
      confirmDelete: state.confirmDelete,
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
        onCancelDeleteModal: () => {
          dispatch(cancelDeleteModal());
        },
        onOpenDeleteModal: () => {
          dispatch(openDeleteModal());
        },
      },
    } as DispatchProps;
  }
)(Edit);
