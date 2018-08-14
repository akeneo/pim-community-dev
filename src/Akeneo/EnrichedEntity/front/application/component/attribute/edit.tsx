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
  attributeEditionCancel,
} from 'akeneoenrichedentity/domain/event/attribute/edit';
import {AttributeType} from 'akeneoenrichedentity/domain/model/attribute/attribute';
import {createAttribute} from 'akeneoenrichedentity/application/action/attribute/create';
// import Dropdown, {DropdownElement} from 'akeneoenrichedentity/application/component/app/dropdown';

interface StateProps {
  context: {
    locale: string;
  };
  data: {
    code: string;
    labels: {
      [localeCode: string]: string;
    };
    type: AttributeType;
    valuePerLocale: boolean;
    valuePerChannel: boolean;
  };
  errors: ValidationError[];
}

interface DispatchProps {
  events: {
    onLabelUpdated: (value: string, locale: string) => void;
    onCancel: () => void;
    onSubmit: () => void;
  };
}

interface EditProps extends StateProps, DispatchProps {}

// const AttributeTypeItemView = ({
//   element,
//   isActive,
//   onClick,
// }: {
//   element: DropdownElement;
//   isActive: boolean;
//   onClick: (element: DropdownElement) => void;
// }) => {
//   const className = `AknDropdown-menuLink AknDropdown-menuLink--withImage ${
//     isActive ? 'AknDropdown-menuLink--active' : ''
//   }`;

//   return (
//     <div
//       className={className}
//       data-identifier={element.identifier}
//       onClick={() => onClick(element)}
//       onKeyPress={event => {
//         if (' ' === event.key) onClick(element);
//       }}
//       tabIndex={0}
//     >
//       <img
//         className="AknDropdown-menuLinkImage"
//         src={`bundles/pimui/images/attribute/icon-${element.identifier}.svg`}
//       />
//       <span>{element.label}</span>
//     </div>
//   );
// };

class Edit extends React.Component<EditProps> {
  private labelInput: HTMLInputElement;
  public props: EditProps;

  componentDidMount() {
    if (this.labelInput) {
      this.labelInput.focus();
    }
  }

  private onLabelUpdate = (event: any) => {
    this.props.events.onLabelUpdated(event.target.value, this.props.context.locale);
  };

  private onKeyPress = (event: any) => {
    if ('Enter' === event.key) {
      this.props.events.onSubmit();
    }
  };

  render(): JSX.Element | JSX.Element[] | null {
    return (
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
        {/* <div className="AknFieldContainer" data-code="type">
          <div className="AknFieldContainer-header">
            <label
              className="AknFieldContainer-label"
              htmlFor="pim_enriched_entity.attribute.create.input.type"
            >
              {__('pim_enriched_entity.attribute.create.input.type')}
            </label>
          </div>
          <div className="AknFieldContainer-inputContainer">
            <Dropdown
              ItemView={AttributeTypeItemView}
              label={__('pim_enriched_entity.attribute.create.input.type')}
              elements={this.getTypeOptions()}
              selectedElement={this.props.data.type}
              onSelectionChange={this.onTypeUpdate}
            />
          </div>
          {getErrorsView(this.props.errors, 'type')}
        </div> */}
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
