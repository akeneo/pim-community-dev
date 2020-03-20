type ValidationError = {
  property: string;
  message: string;
};

const filterErrors = (errors: ValidationError[], property: string) =>
  errors.filter((error: ValidationError) => error.property === property);

export {ValidationError, filterErrors};
