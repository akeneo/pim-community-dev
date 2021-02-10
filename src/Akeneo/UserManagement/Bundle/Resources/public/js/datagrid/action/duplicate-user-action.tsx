import React from 'react';
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

    const closeApp = () => {
      ReactDOM.unmountComponentAtNode(container);
      document.body.removeChild(container);
    };
    const onDuplicateSuccess = (duplicatedUserId: string) => {
      closeApp();
      Routing.redirect(Routing.generate('pim_user_edit', {identifier: duplicatedUserId}));
    };

    ReactDOM.render(
      <>
        <DuplicateUserApp
          userId={this.model.get(this.propertyName)}
          onCancel={closeApp}
          onDuplicateSuccess={onDuplicateSuccess}
        />
      </>,
      container
    );
  }
}

export = DuplicateUserAction;
