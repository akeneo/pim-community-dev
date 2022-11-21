import {IdentifierGenerator, PROPERTY_NAMES} from '../../models';

const initialGenerator: IdentifierGenerator = {
  code: 'initialCode',
  labels: {
    en_US: 'Initial Label',
  },
  conditions: [],
  structure: [{type: PROPERTY_NAMES.FREE_TEXT, string: 'AKN'}],
  delimiter: null,
  target: 'sku',
};

export {initialGenerator};
