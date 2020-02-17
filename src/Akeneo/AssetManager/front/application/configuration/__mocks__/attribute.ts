// @ts-nocheck
'use strict';

const config = {
  media_file: {
    denormalize: require('akeneoassetmanager/domain/model/attribute/type/media-file'),
  },
  text: {
    denormalize: require('akeneoassetmanager/domain/model/attribute/type/text'),
  },
  number: {
    denormalize: require('akeneoassetmanager/domain/model/attribute/type/number'),
  },
  option: {
    denormalize: require('akeneoassetmanager/domain/model/attribute/type/option'),
  },
  option_collection: {
    denormalize: require('akeneoassetmanager/domain/model/attribute/type/option-collection'),
  },
  media_link: {
    denormalize: require('akeneoassetmanager/domain/model/attribute/type/media-link'),
  },
};

const real = jest.requireActual('akeneoassetmanager/application/configuration/attribute');
const mock = jest.genMockFromModule('akeneoassetmanager/application/configuration/attribute');

mock.getAttributeTypes = real.getTypes(config);
mock.getAttributeIcon = real.getIcon(config);
mock.getAttributeView = real.getView(config);
mock.getAttributeDenormalizer = real.getDenormalizer(config);
mock.getAttributeReducer = real.getReducer(config);

module.exports = mock;
