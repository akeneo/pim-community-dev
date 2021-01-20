import React from 'react';
import { useTranslate } from '@akeneo-pim-community/legacy';
import { Helper } from 'akeneo-design-system';
import { formatParameters } from '../models/validation-error';
var InputErrors = function (_a) {
    var _b = _a.errors, errors = _b === void 0 ? [] : _b;
    var translate = useTranslate();
    if (0 === errors.length)
        return null;
    return (React.createElement(React.Fragment, null, formatParameters(errors).map(function (error, key) { return (React.createElement(Helper, { inline: true, level: "error", key: key }, translate(error.messageTemplate, error.parameters, error.plural))); })));
};
export { InputErrors };
//# sourceMappingURL=InputErrors.js.map