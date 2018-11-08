import * as React from 'react';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
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
      </React.Fragment>
    );
  }
}

export const view = OptionView;
