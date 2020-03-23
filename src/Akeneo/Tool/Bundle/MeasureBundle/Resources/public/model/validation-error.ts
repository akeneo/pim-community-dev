type ValidationError = {
  messageTemplate: string;
  parameters: {
    [key: string]: string;
  };
  message: string;
  propertyPath: string;
  invalidValue: any;
};

const filterErrors = (errors: ValidationError[], propertyPath: string) =>
  errors
    .filter(error => error.propertyPath.startsWith(propertyPath))
    .map(error => ({...error, propertyPath: error.propertyPath.replace(propertyPath, '')}));

const getErrorsForPath = (errors: ValidationError[], propertyPath: string) =>
  errors.filter(error => error.propertyPath === propertyPath);

export {ValidationError, filterErrors, getErrorsForPath};
