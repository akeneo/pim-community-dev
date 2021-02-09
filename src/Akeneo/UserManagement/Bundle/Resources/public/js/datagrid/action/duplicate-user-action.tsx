import React from 'react';
import {pimTheme} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import ReactDOM from 'react-dom';
import {DuplicateUserApp} from '@akeneo-pim-community/user-ui';

const Routing = require('pim/router');
const AbstractAction = require('oro/datagrid/abstract-action');

class DuplicateUserAction extends AbstractAction {
  /**
   * {@inheritdoc}
   */
  execute() {
    const container = document.createElement('div');
    document.body.appendChild(container);

    const closeModal = () => {
      ReactDOM.unmountComponentAtNode(container);
      document.body.removeChild(container);
    };
    const onDuplicateSuccess = (duplicatedUserId: string) => {
      closeModal();
      Routing.redirect(Routing.generate('pim_user_edit', {identifier: duplicatedUserId}));
    };

    ReactDOM.render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <DuplicateUserApp
            userId={this.model.get(this.propertyName)}
            onCancel={closeModal}
            onDuplicateSuccess={onDuplicateSuccess}
          />
        </ThemeProvider>
      </DependenciesProvider>,
      container
    );
  }
}

export = DuplicateUserAction;
