type ValidationError = {
  property: string;
  message: string;
};

const filterErrors = (errors: ValidationError[], property: string) =>
  errors
    .filter(error => error.property.startsWith(property))
    .map(error => ({...error, property: error.property.replace(property, '')}));

export {ValidationError, filterErrors};
