'use strict';

/**
 * Minimal replacement for the static `extend` method of Backbone classes, for legacy modules that only relied on
 * Backbone for inheritance (`Parent.extend({...})` and `Parent.prototype.method.apply(this, arguments)`).
 *
 * Usage: `MyClass.extend = classExtend;`
 */
function classExtend(protoProps, staticProps) {
  const parent = this;
  const child =
    protoProps && Object.prototype.hasOwnProperty.call(protoProps, 'constructor')
      ? protoProps.constructor
      : function () {
          return parent.apply(this, arguments);
        };

  Object.assign(child, parent, staticProps);
  child.prototype = Object.create(parent.prototype, {
    constructor: {value: child, writable: true, configurable: true},
  });
  Object.assign(child.prototype, protoProps);
  child.__super__ = parent.prototype;

  return child;
}

module.exports = classExtend;
