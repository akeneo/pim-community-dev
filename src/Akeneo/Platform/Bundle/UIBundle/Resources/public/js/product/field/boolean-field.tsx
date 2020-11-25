import React from 'react';
import ReactDOM from 'react-dom';
import {BooleanInput, pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';

const Field = require('pim/field');
const translate = require('oro/translator');

/**
 * Boolean field
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BooleanField extends (Field as {new (config: any): any}) {
  renderInput(templateContext: any) {
    const container = document.createElement('div');

    ReactDOM.render(
      <ThemeProvider theme={pimTheme}>
        <BooleanInput
          clearable={true}
          value={templateContext.value.data}
          onChange={(value: boolean | null) => {
            this.setCurrentValue(value);
            this.render();
          }}
          clearLabel={translate('pim_common.clear_value')}
          yesLabel={translate('pim_common.yes')}
          noLabel={translate('pim_common.no')}
          readOnly={templateContext.editMode === 'view'}
        />
      </ThemeProvider>,
      container
    );

    return container;
  }
}

module.exports = BooleanField;
