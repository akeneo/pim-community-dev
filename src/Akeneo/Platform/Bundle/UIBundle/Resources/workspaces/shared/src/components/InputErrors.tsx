import React, {ReactElement} from 'react';
import {ValidationError, formatParameters} from '@akeneo-pim-community/shared';
import {Translate} from '@akeneo-pim-community/legacy-bridge';
import {Helper, HelperProps} from 'akeneo-design-system';

const inputErrors = (translate: Translate, errors: ValidationError[] = []): ReactElement<HelperProps>[] =>
  formatParameters(errors).map((error, key) => (
    <Helper key={key} level='error' inline={true}>
      {translate(error.messageTemplate, error.parameters, error.plural)}
    </Helper>
  ));

export {inputErrors};
