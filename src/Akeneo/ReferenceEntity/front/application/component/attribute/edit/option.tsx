import * as React from 'react';
// import __ from 'akeneoreferenceentity/tools/translator';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
// import {getErrorsView} from 'akeneoreferenceentity/application/component/app/validation-error';
import {OptionAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/option';

class OptionView extends React.Component<
  {
    attribute: OptionAttribute;
    errors: ValidationError[];
    locale: string;
  }
> {
  render() {
    return (
      <React.Fragment>
        {/*<div className="AknFieldContainer" data-code="optionType">*/}
          {/*<div className="AknFieldContainer-header AknFieldContainer-header--light">*/}
            {/*<label className="AknFieldContainer-label" htmlFor="pim_reference_entity.attribute.edit.input.option_type">*/}
              {/*{__('pim_reference_entity.attribute.edit.input.option_type')}*/}
            {/*</label>*/}
          {/*</div>*/}
          {/*<div className="AknFieldContainer-inputContainer">*/}
            {/*<input*/}
              {/*type="text"*/}
              {/*className="AknTextField AknTextField--light AknTextField--disabled"*/}
              {/*id="pim_reference_entity.attribute.edit.input.option_type"*/}
              {/*name="option_type"*/}
              {/*value={value}*/}
              {/*readOnly*/}
              {/*tabIndex={-1}*/}
            {/*/>*/}
          {/*</div>*/}
          {/*{getErrorsView(this.props.errors, 'optionType')}*/}
        {/*</div>*/}
      </React.Fragment>
    );
  }
}

export const view = OptionView;
