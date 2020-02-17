// @ts-nocheck
'use strict';

const config = {
  image: {
    denormalize: require('akeneoreferenceentity/domain/model/record/data/file'),
  },
  text: {
    denormalize: require('akeneoreferenceentity/domain/model/record/data/text'),
  },
  number: {
    denormalize: require('akeneoreferenceentity/domain/model/record/data/number'),
  },
  record: {
    denormalize: require('akeneoreferenceentity/domain/model/record/data/record'),
  },
  record_collection: {
    denormalize: require('akeneoreferenceentity/domain/model/record/data/record-collection'),
  },
  option: {
    denormalize: require('akeneoreferenceentity/domain/model/record/data/option'),
  },
  option_collection: {
    denormalize: require('akeneoreferenceentity/domain/model/record/data/option-collection'),
  },
};

const real = jest.requireActual('akeneoreferenceentity/application/configuration/value');
const mock = jest.genMockFromModule('akeneoreferenceentity/application/configuration/value');

mock.getDataDenormalizer = real.getDenormalizer(config);
mock.getDataFieldView = real.getFieldView(config);
mock.getDataCellView = real.getCellView(config);
mock.hasDataCellView = real.hasCellView(config);
mock.getDataFilterView = real.getFilterView(config);
mock.hasDataFilterView = real.hasDataFilterView(config);

module.exports = mock;
