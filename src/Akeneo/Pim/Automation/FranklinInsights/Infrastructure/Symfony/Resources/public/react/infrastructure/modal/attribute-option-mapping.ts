import * as Backbone from 'backbone';
import * as _ from 'underscore';

import {translate} from '../translator';

const FormBuilder = require('pim/form-builder');
const template = require('pim/template/common/modal-centered');

export const createAttributeOptionMappingModal = async (
  familyCode: string,
  familyLabel: string,
  attributeCode: string,
  franklinAttributeCode: string,
  franklinAttributeLabel: string
) => {
  const form = await FormBuilder.build('akeneo-franklin-insights-settings-attribute-options-mapping-edit');

  const formContent = form.getExtension('content');
  await formContent.initializeMapping(familyCode, attributeCode, franklinAttributeCode);

  const modal = new (Backbone as any).BootstrapModal({
    modalOptions: {
      backdrop: 'static',
      keyboard: false
    },
    okCloses: false,
    title: translate('akeneo_franklin_insights.entity.attribute_options_mapping.module.edit.title', {
      familyLabel,
      franklinAttributeLabel
    }),
    content: form,
    template: _.template(template),
    innerClassName: 'AknFullPage--full AknFullPage--fixedWidth',
    okText: ''
  });

  modal.open();
};
