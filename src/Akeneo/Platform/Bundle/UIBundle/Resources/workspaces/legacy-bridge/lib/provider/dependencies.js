"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.dependencies = void 0;
var router = require('pim/router');
var translate = require('oro/translator');
var viewBuilder = require('pim/form-builder');
var messenger = require('oro/messenger');
var userContext = require('pim/user-context');
var securityContext = require('pim/security-context');
var mediator = require('oro/mediator');
var dependencies = {
    router: router,
    translate: translate,
    viewBuilder: viewBuilder,
    notify: messenger.notify,
    user: userContext,
    security: {
        isGranted: securityContext.isGranted.bind(securityContext),
    },
    mediator: mediator,
};
exports.dependencies = dependencies;
//# sourceMappingURL=dependencies.js.map