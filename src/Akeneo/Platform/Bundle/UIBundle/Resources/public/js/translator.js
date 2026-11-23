/**
 * @deprecated Legacy module ID kept for backward compatibility.
 * The implementation lives in @akeneo-pim-community/shared. This shim also registers the `_.__()` helper used by
 * the legacy underscore templates.
 */
const _ = require('underscore');
const {translate} = require('@akeneo-pim-community/shared');

_.mixin({__: translate});

module.exports = translate;
