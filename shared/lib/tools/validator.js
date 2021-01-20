var Ajv = require('ajv');
var ajv = new Ajv();
export var isValidAgainstSchema = function (data, schema) {
    try {
        return ajv.validate(schema, data);
    }
    catch (e) {
        throw Error(e.message);
    }
};
export var validateAgainstSchema = function (data, schema) {
    var isValid = isValidAgainstSchema(data, schema);
    if (!isValid) {
        console.error('The data does not match the JSON schema', ajv.errors);
        throw Error('The data does not match the JSON schema');
    }
    return data;
};
//# sourceMappingURL=validator.js.map