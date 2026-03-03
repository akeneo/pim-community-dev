// require instead of import : https://github.com/epoberezkin/ajv/pull/748
const Ajv = require('ajv');

const ajv = new Ajv();

export const isValidAgainstSchema = <T>(data: any, schema: object): data is T => {
  try {
    return ajv.validate(schema, data);
  } catch (e: any) {
    throw Error(e.message);
  }
};

export const validateAgainstSchema = <T>(data: any, schema: object): T => {
  const isValid = isValidAgainstSchema<T>(data, schema);

  if (!isValid) {
    console.error('The data does not match the JSON schema', ajv.errors);
    throw Error('The data does not match the JSON schema');
  }

  return data;
};
