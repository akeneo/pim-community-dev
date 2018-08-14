import * as React from 'react';
import {connect} from 'react-redux';
import __ from 'akeneoenrichedentity/tools/translator';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';
import Flag from 'akeneoenrichedentity/tools/component/flag';
import {getErrorsView} from 'akeneoenrichedentity/application/component/app/validation-error';
import {EditState} from 'akeneoenrichedentity/application/reducer/enriched-entity/edit';
import Switch from 'akeneoenrichedentity/application/component/app/switch';
import {
  attributeEditionLabelUpdated,
  attributeEditionRequiredUpdated,
  attributeEditionAdditionalPropertyUpdated,
  attributeEditionCancel,
} from 'akeneoenrichedentity/domain/event/attribute/edit';
import {
  AttributeType,
  AdditionalProperty,
  NormalizedAttribute
} from 'akeneoenrichedentity/domain/model/attribute/attribute';
import {createAttribute} from 'akeneoenrichedentity/application/action/attribute/create';
import TextProperty from 'akeneoenrichedentity/application/component/attribute/edit/text';
import ImageProperty from 'akeneoenrichedentity/application/component/attribute/edit/image';

interface StateProps {
  context: {
    locale: string;
  };
  data: NormalizedAttribute;
  errors: ValidationError[];
}

interface DispatchProps {
  events: {
    onLabelUpdated: (value: string, locale: string) => void;
    onRequiredUpdated: (required: boolean) => void;
    onAdditionalPropertyUpdated: (property: string, value: AdditionalProperty) => void;
    onCancel: () => void;
    onSubmit: () => void;
  };
}

interface EditProps extends StateProps, DispatchProps {}

class Edit extends React.Component<EditProps> {
  private labelInput: HTMLInputElement;
  private allowedAdditionalData = {
    [AttributeType.Text]: TextProperty,
    [AttributeType.Image]: ImageProperty,
  };
  private additionalProperties: JSX.Element;
  public props: EditProps;

  componentDidMount() {
    if (this.labelInput) {
      this.labelInput.focus();
    }

    this.getAdditionalProperty();
  }

  private onLabelUpdate = (event: any) => {
    this.props.events.onLabelUpdated(event.target.value, this.props.context.locale);
  };

  private onKeyPress = (event: any) => {
    if ('Enter' === event.key) {
      this.props.events.onSubmit();
    }
  };

  private getAdditionalProperty = (): void => {
    const AdditionalProperty = this.allowedAdditionalData[this.props.data.type as AttributeType];
    this.additionalProperties = <AdditionalProperty
      attribute={this.props.data}
      onAdditionalPropertyUpdated={this.props.events.onAdditionalPropertyUpdated}
      errors={this.props.errors}
    />;
    this.forceUpdate();
  };

  render(): JSX.Element | JSX.Element[] | null {
    return (
      <div>
        <div className="AknFormContainer">
          <div className="AknFieldContainer" data-code="label">
            <div className="AknFieldContainer-header">
              <label
                className="AknFieldContainer-label"
                htmlFor="pim_enriched_entity.attribute.create.input.label"
              >
                {__('pim_enriched_entity.attribute.create.input.label')}
              </label>
            </div>
            <div className="AknFieldContainer-inputContainer">
              <input
                type="text"
                ref={(input: HTMLInputElement) => {
                  this.labelInput = input;
                }}
                className="AknTextField"
                id="pim_enriched_entity.attribute.create.input.label"
                name="label"
                value={this.props.data.labels[this.props.context.locale]}
                onChange={this.onLabelUpdate}
                onKeyPress={this.onKeyPress}
              />
              <Flag locale={this.props.context.locale} displayLanguage={false} />
            </div>
            {getErrorsView(this.props.errors, 'labels')}
          </div>
          <div className="AknFieldContainer" data-code="code">
            <div className="AknFieldContainer-header">
              <label
                className="AknFieldContainer-label"
                htmlFor="pim_enriched_entity.attribute.create.input.code"
              >
                {__('pim_enriched_entity.attribute.create.input.code')}
              </label>
            </div>
            <div className="AknFieldContainer-inputContainer">
              <input
                type="text"
                className="AknTextField"
                id="pim_enriched_entity.attribute.create.input.code"
                name="code"
                value={this.props.data.code}
                readOnly
              />
            </div>
          </div>
          <div className="AknFieldContainer" data-code="valuePerLocale">
            <div className="AknFieldContainer-header">
              <label
                className="AknFieldContainer-label"
                htmlFor="pim_enriched_entity.attribute.create.input.value_per_locale"
              >
                {__('pim_enriched_entity.attribute.create.input.value_per_locale')}
              </label>
            </div>
            <div className="AknFieldContainer-inputContainer">
              <Switch
                id="pim_enriched_entity.attribute.create.input.value_per_locale"
                value={this.props.data.valuePerLocale}
                readOnly
              />
            </div>
            {getErrorsView(this.props.errors, 'valuePerLocale')}
          </div>
          <div className="AknFieldContainer" data-code="valuePerChannel">
            <div className="AknFieldContainer-header">
              <label
                className="AknFieldContainer-label"
                htmlFor="pim_enriched_entity.attribute.create.input.value_per_channel"
              >
                {__('pim_enriched_entity.attribute.create.input.value_per_channel')}
              </label>
            </div>
            <div className="AknFieldContainer-inputContainer">
              <Switch
                id="pim_enriched_entity.attribute.create.input.value_per_channel"
                value={this.props.data.valuePerChannel}
                readOnly
              />
            </div>
            {getErrorsView(this.props.errors, 'valuePerChannel')}
          </div>
          <div className="AknFieldContainer" data-code="required">
            <div className="AknFieldContainer-header">
              <label
                className="AknFieldContainer-label"
                htmlFor="pim_enriched_entity.attribute.create.input.required"
              >
                {__('pim_enriched_entity.attribute.create.input.required')}
              </label>
            </div>
            <div className="AknFieldContainer-inputContainer">
              <Switch
                id="pim_enriched_entity.attribute.create.input.required"
                value={this.props.data.required}
                onChange={this.props.events.onRequiredUpdated}
              />
              {getErrorsView(this.props.errors, 'required')}
            </div>
          </div>
        </div>
        {this.additionalProperties}
      </div>
    );
  }
}

export default connect(
  (state: EditState): StateProps => {
    const locale = undefined === state.user || undefined === state.user.catalogLocale ? '' : state.user.catalogLocale;

    return {
      data: state.attribute.data,
      errors: state.attribute.errors,
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
        onRequiredUpdated: (required: boolean) => {
          dispatch(attributeEditionRequiredUpdated(required));
        },
        onAdditionalPropertyUpdated: (property: string, value: AdditionalProperty) => {
          dispatch(attributeEditionAdditionalPropertyUpdated(property, value));
        },
        onCancel: () => {
          dispatch(attributeEditionCancel());
        },
        onSubmit: () => {
          dispatch(createAttribute());
        },
      },
    } as DispatchProps;
  }
)(Edit);
