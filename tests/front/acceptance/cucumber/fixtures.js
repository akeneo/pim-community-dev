const { createProduct, createProductWithLabels } = require('./fixtures/product');
const createLocale = require('./fixtures/locale');
const createChannel = require('./fixtures/channel');
const createUser = require('./fixtures/user');
const createAssociationType = require('./fixtures/association-type');

module.exports = {
    createChannel,
    createLocale,
    createProduct,
    createProductWithLabels,
    createUser,
    createAssociationType
};
