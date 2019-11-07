// require instead of import : https://github.com/epoberezkin/ajv/pull/748
const Ajv = require('ajv');

const ajv = new Ajv();

export const isValidAgainstSchema = (data: any, schema: object): any => {
  return ajv.validate(schema, data);
};

export const validateAgainstSchema = (data: any, schema: object): any => {
  const isValid = isValidAgainstSchema(data, schema);

  if (!isValid) {
    console.warn('validator errors', ajv.errors);
    throw Error('The value does not match the JSON schema');
  }

  return data;
};

export default validateAgainstSchema;
