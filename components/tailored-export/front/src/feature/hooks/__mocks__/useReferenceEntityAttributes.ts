import {ReferenceEntityAttribute} from '../../models';

const useReferenceEntityAttributes = (): ReferenceEntityAttribute[] => [
  {
    code: 'name',
    type: 'text',
    identifier: 'name_1234',
    labels: {},
    value_per_channel: true,
    value_per_locale: false,
  },
];

export {useReferenceEntityAttributes};
