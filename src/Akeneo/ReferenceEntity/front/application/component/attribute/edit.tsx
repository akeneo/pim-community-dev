import * as React from 'react';
import {connect} from 'react-redux';
import __ from 'akeneoreferenceentity/tools/translator';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import Flag from 'akeneoreferenceentity/tools/component/flag';
import {getErrorsView} from 'akeneoreferenceentity/application/component/app/validation-error';
import {EditState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import Checkbox from 'akeneoreferenceentity/application/component/app/checkbox';
import {
  attributeEditionLabelUpdated,
  attributeEditionIsRequiredUpdated,
  attributeEditionAdditionalPropertyUpdated,
  attributeEditionCancel,
} from 'akeneoreferenceentity/domain/event/attribute/edit';
import Attribute, {
  AdditionalProperty,
  denormalizeAttribute,
} from 'akeneoreferenceentity/domain/model/attribute/attribute';
import {AttributeType} from 'akeneoreferenceentity/domain/model/attribute/minimal';
import {saveAttribute} from 'akeneoreferenceentity/application/action/attribute/edit';
import TextPropertyView from 'akeneoreferenceentity/application/component/attribute/edit/text';
import ImagePropertyView from 'akeneoreferenceentity/application/component/attribute/edit/image';
import {createLocaleFromCode} from 'akeneoreferenceentity/domain/model/locale';
import {TextAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/text';
import {ImageAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/image';
import {deleteAttribute} from 'akeneoreferenceentity/application/action/attribute/list';
import AttributeIdentifier from 'akeneoreferenceentity/domain/model/attribute/identifier';

interface StateProps {
  context: {
    locale: string;
  };
  isSaving: boolean;
  isActive: boolean;
  attribute: Attribute;
  errors: ValidationError[];
}

interface DispatchProps {
  events: {
    onLabelUpdated: (value: string, locale: string) => void;
    onIsRequiredUpdated: (isRequired: boolean) => void;
    onAdditionalPropertyUpdated: (property: string, value: AdditionalProperty) => void;
    onAttributeDelete: (attributeIdentifier: AttributeIdentifier) => void;
    onCancel: () => void;
    onSubmit: () => void;
  };
}

interface EditProps extends StateProps, DispatchProps {}

class InvalidAttributeTypeError extends Error {}

const getAdditionalProperty = (
  attribute: Attribute,
  onAdditionalPropertyUpdated: (property: string, value: AdditionalProperty) => void,
  onSubmit: () => void,
  errors: ValidationError[]
): JSX.Element => {
  switch (attribute.type) {
    case AttributeType.Text:
      return (
        <TextPropertyView
          attribute={attribute as TextAttribute}
          onAdditionalPropertyUpdated={onAdditionalPropertyUpdated}
          onSubmit={onSubmit}
          errors={errors}
        />
      );
    case AttributeType.Image:
      return (
        <ImagePropertyView
          attribute={attribute as ImageAttribute}
          onAdditionalPropertyUpdated={onAdditionalPropertyUpdated}
          onSubmit={onSubmit}
          errors={errors}
        />
      );
    default:
      throw new InvalidAttributeTypeError(
        `There is no view capable of rendering attribute of type "${attribute.type}"`
      );
  }
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
    if ('Enter' === event.key) {
      this.props.events.onSubmit();
    }
  };

  private onAttributeDelete() {
    const message = __('pim_reference_entity.attribute.delete.confirm');
    if (confirm(message)) {
      this.props.events.onAttributeDelete(this.props.attribute.getIdentifier());
    }
  }

  render(): JSX.Element | JSX.Element[] | null {
    return (
      <React.Fragment>
        <div className={`AknQuickEdit ${!this.props.isActive ? 'AknQuickEdit--hidden' : ''}`} ref="quickEdit">
          <div className={`AknLoadingMask ${!this.props.isSaving ? 'AknLoadingMask--hidden' : ''}`} />
          <div className="AknSubsection">
            <header className="AknSubsection-title AknSubsection-title--sticky">
              {__('pim_reference_entity.attribute.edit.common.title')}
              <span
                title={__('pim_reference_entity.attribute.edit.save')}
                className="AknButtonList-item AknButton-squareIcon AknButton-squareIcon--small AknButton-squareIcon--validate"
                tabIndex={0}
                onClick={this.props.events.onSubmit}
                onKeyPress={(event: React.KeyboardEvent<HTMLElement>) => {
                  if (' ' === event.key) {
                    this.props.events.onSubmit();
                  }
                }}
              />
            </header>
            <div className="AknFormContainer AknFormContainer--expanded">
              <div className="AknFieldContainer" data-code="label">
                <div className="AknFieldContainer-header AknFieldContainer-header--light">
                  <label className="AknFieldContainer-label" htmlFor="pim_reference_entity.attribute.edit.input.label">
                    {__('pim_reference_entity.attribute.edit.input.label')}
                  </label>
                </div>
                <div className="AknFieldContainer-inputContainer">
                  <input
                    type="text"
                    ref={(input: HTMLInputElement) => {
                      this.labelInput = input;
                    }}
                    className="AknTextField AknTextField--light"
                    id="pim_reference_entity.attribute.edit.input.label"
                    name="label"
                    value={this.props.attribute.getLabel(this.props.context.locale, false)}
                    onChange={this.onLabelUpdate}
                    onKeyPress={this.onKeyPress}
                  />
                  <Flag
                    locale={createLocaleFromCode(this.props.context.locale)}
                    displayLanguage={false}
                    className="AknFieldContainer-inputSides"
                  />
                </div>
                {getErrorsView(this.props.errors, 'labels')}
              </div>
              <div className="AknFieldContainer" data-code="code">
                <div className="AknFieldContainer-header AknFieldContainer-header--light">
                  <label className="AknFieldContainer-label" htmlFor="pim_reference_entity.attribute.edit.input.code">
                    {__('pim_reference_entity.attribute.edit.input.code')}
                  </label>
                </div>
                <div className="AknFieldContainer-inputContainer">
                  <input
                    type="text"
                    className="AknTextField AknTextField--light AknTextField--disabled"
                    id="pim_reference_entity.attribute.edit.input.code"
                    name="code"
                    value={this.props.attribute.code.stringValue()}
                    readOnly
                    tabIndex={-1}
                  />
                </div>
              </div>
              <div className="AknFieldContainer AknFieldContainer--packed" data-code="valuePerChannel">
                <div className="AknFieldContainer-header">
                  <label
                    className="AknFieldContainer-label"
                    htmlFor="pim_reference_entity.attribute.edit.input.value_per_channel"
                  >
                    <Checkbox
                      id="pim_reference_entity.attribute.edit.input.value_per_channel"
                      value={this.props.attribute.valuePerChannel}
                      readOnly
                    />
                    {__('pim_reference_entity.attribute.edit.input.value_per_channel')}
                  </label>
                </div>
                {getErrorsView(this.props.errors, 'valuePerChannel')}
              </div>
              <div className="AknFieldContainer AknFieldContainer--packed" data-code="valuePerLocale">
                <div className="AknFieldContainer-header">
                  <label
                    className="AknFieldContainer-label"
                    htmlFor="pim_reference_entity.attribute.edit.input.value_per_locale"
                  >
                    <Checkbox
                      id="pim_reference_entity.attribute.edit.input.value_per_locale"
                      value={this.props.attribute.valuePerLocale}
                      readOnly
                    />
                    {__('pim_reference_entity.attribute.edit.input.value_per_locale')}
                  </label>
                </div>
                {getErrorsView(this.props.errors, 'valuePerLocale')}
              </div>
              <div className="AknFieldContainer AknFieldContainer--packed" data-code="isRequired">
                <div className="AknFieldContainer-header">
                  <label
                    className="AknFieldContainer-label AknFieldContainer-label--inline"
                    htmlFor="pim_reference_entity.attribute.edit.input.is_required"
                  >
                    <Checkbox
                      id="pim_reference_entity.attribute.edit.input.is_required"
                      value={this.props.attribute.isRequired}
                      onChange={this.props.events.onIsRequiredUpdated}
                    />
                    <span
                      onClick={() => {
                        this.props.events.onIsRequiredUpdated(!this.props.attribute.isRequired);
                      }}
                    >
                      {__('pim_reference_entity.attribute.edit.input.is_required')}
                    </span>
                  </label>
                </div>
                {getErrorsView(this.props.errors, 'isRequired')}
              </div>
            </div>
          </div>
          <div className="AknSubsection">
            <header className="AknSubsection-title AknSubsection-title--sticky" style={{top: 0, paddingTop: '10px'}}>
              {__('pim_reference_entity.attribute.edit.additional.title')}
            </header>
            <div className="AknFormContainer AknFormContainer--expanded">
              {getAdditionalProperty(
                this.props.attribute,
                this.props.events.onAdditionalPropertyUpdated,
                this.props.events.onSubmit,
                this.props.errors
              )}
            </div>
          </div>
          <div
            className="AknButton AknButton--delete"
            tabIndex={0}
            onKeyPress={(event: React.KeyboardEvent<HTMLDivElement>) => {
              if (' ' === event.key) {
                this.onAttributeDelete();
              }
            }}
            onClick={() => {
              this.onAttributeDelete();
            }}
          >
            {__('pim_reference_entity.attribute.edit.delete')}
          </div>
        </div>
      </React.Fragment>
    );
  }
}

export default connect(
  (state: EditState): StateProps => {
    const locale = undefined === state.user || undefined === state.user.catalogLocale ? '' : state.user.catalogLocale;

    return {
      isActive: state.attribute.isActive,
      attribute: denormalizeAttribute(state.attribute.data),
      errors: state.attribute.errors,
      isSaving: state.attribute.isSaving,
      context: {
        locale: locale,
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
        onAdditionalPropertyUpdated: (property: string, value: AdditionalProperty) => {
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
