// @ts-nocheck
'use strict';

const config = {
  image: {
    denormalize: require('akeneoreferenceentity/domain/model/attribute/type/image'),
  },
  text: {
    denormalize: require('akeneoreferenceentity/domain/model/attribute/type/text'),
  },
  number: {
    denormalize: require('akeneoreferenceentity/domain/model/attribute/type/number'),
  },
  record: {
    denormalize: require('akeneoreferenceentity/domain/model/attribute/type/record'),
  },
  record_collection: {
    denormalize: require('akeneoreferenceentity/domain/model/attribute/type/record-collection'),
  },
  option: {
    denormalize: require('akeneoreferenceentity/domain/model/attribute/type/option'),
  },
  option_collection: {
    denormalize: require('akeneoreferenceentity/domain/model/attribute/type/option-collection'),
  },
};

const real = jest.requireActual('akeneoreferenceentity/application/configuration/attribute');
const mock = jest.genMockFromModule('akeneoreferenceentity/application/configuration/attribute');

mock.getAttributeTypes = real.getTypes(config);
mock.getAttributeIcon = real.getIcon(config);
mock.getAttributeView = real.getView(config);
mock.getAttributeDenormalizer = real.getDenormalizer(config);
mock.getAttributeReducer = real.getReducer(config);

module.exports = mock;
