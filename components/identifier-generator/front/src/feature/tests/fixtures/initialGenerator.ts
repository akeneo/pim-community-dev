import {CONDITION_NAMES, IdentifierGenerator, PROPERTY_NAMES, TEXT_TRANSFORMATION} from '../../models';

const initialGenerator: IdentifierGenerator = {
  code: 'initialCode',
  labels: {
    en_US: 'Initial Label',
  },
  conditions: [{type: CONDITION_NAMES.ENABLED, value: true}],
  structure: [{type: PROPERTY_NAMES.FREE_TEXT, string: 'AKN'}],
  delimiter: '-',
  target: 'sku',
  text_transformation: TEXT_TRANSFORMATION.NO,
};

export default initialGenerator;
