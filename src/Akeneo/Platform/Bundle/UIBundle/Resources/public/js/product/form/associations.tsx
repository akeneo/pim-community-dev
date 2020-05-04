import React from 'react';
import ReactDOM from 'react-dom';
import {AssociationGrid} from './associations/association-grid';
// import {ThemeProvider} from 'styled-components';

const Form = require('pim/form');
const UserContext = require('pim/user-context');

// const updateValueMiddleware = (formView: AssetTabForm) => {
//   return () => (next: any) => (action: any) => {
//     if ('VALUE_CHANGED' === action.type) {
//       const valueToUpdate = formView.getFormData().values[action.value.attribute.code].find((value: LegacyValue) => {
//         return value.locale === action.value.locale && value.scope === action.value.channel;
//       });

//       valueToUpdate.data = action.value.data;
//       formView.getRoot().trigger('pim_enrich:form:entity:update_state');
//     }

//     return next(action);
//   };
// };

class AssociationsTabForm extends (Form as {new (meta: any): any}) {
  constructor(meta: any) {
    super(meta);
    this.config = meta.config;
  }

  configure() {
    // Register the asset tab in the sidebar
    this.trigger('tab:register', {
      code: this.code,
      label: 'TODO',
    });

    this.listenTo(UserContext, 'change:catalogLocale', this.updateLocale);
    this.listenTo(UserContext, 'change:catalogScope', this.updateChannel);
    this.listenTo(this.getRoot(), this.getRoot().postUpdateEventName, async () => {
      // const values = await generate(this.getFormData());
    });

    //Validation errors
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:bad_request', this.addErrors);
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:pre_save', this.removeErrors);

    return Form.prototype.configure.apply(this, arguments);
  }

  render() {
    // if (null === this.store.getState().structure.family) {
    //   // this.store.dispatch(updateFamily(this.getFormData().family) as any);
    // }

    ReactDOM.render(
      <AssociationGrid
        name={this.config.datagridName}
        conf={{
          getInitialParams: (_associationType: any) => {
            let params: any = {
              product: this.getFormData().meta.id,
            };
            params['associationType'] = 'associationType';
            params.dataLocale = UserContext.get('catalogLocale');

            return params;
          },
          paramName: 'associationType',
          getParamValue: (_associationType: any) => {
            return 4; //TODO
          },
          getModelIdentifier: (model: {get: (arg0: string) => any}) => {
            return model.get('identifier');
          },
        }}
        onSelectionChange={() => {}}
      />,
      this.el
    );
  }

  addErrors(_event: any) {
    // if (isValidErrorCollection(event.response)) {
    // const errorCollection = denormalizeErrorCollection(event.response);
    // this.store.dispatch(errorsReceived(errorCollection));
    // }
  }

  removeErrors() {
    // this.store.dispatch(errorsRemovedAll());
  }
}

module.exports = AssociationsTabForm;
