type ValidationError = {
  messageTemplate: string;
  parameters: {
    [key: string]: string;
  };
  message: string;
  propertyPath: string;
  invalidValue: any;
  plural?: number;
};

const filterErrors = (errors: ValidationError[], propertyPath: string): ValidationError[] =>
  errors
    .filter((error) => error.propertyPath.startsWith(propertyPath))
    .map((error) => ({...error, propertyPath: error.propertyPath.replace(propertyPath, '')}));

const getErrorsForPath = (errors: ValidationError[], propertyPath: string): ValidationError[] =>
  errors.filter((error) => error.propertyPath === propertyPath);

const formatParameters = (errors: ValidationError[]): ValidationError[] =>
  errors.map((error) => ({
    ...error,
    parameters: Object.keys(error.parameters).reduce(
      (result, key) => ({
        ...result,
        [key.replace('{{ ', '').replace(' }}', '')]: error.parameters[key],
      }),
      {}
    ),
  }));

const partition = <T>(items: T[], condition: (item: T) => boolean): T[][] => {
  return items.reduce(
    (result: T[][], item: T) => {
      result[condition(item) ? 0 : 1].push(item);
      return result;
    },
    [[], []]
  );
};

const partitionErrors = (
  errors: ValidationError[],
  conditions: ((item: ValidationError) => boolean)[]
): ValidationError[][] => {
  const results: ValidationError[][] = [];
  let restErrors = [...errors];

  conditions.forEach((condition) => {
    const [match, rest] = partition<ValidationError>(restErrors, condition);
    results.push(match);
    restErrors = rest;
  });

  return [...results, restErrors];
};

export {ValidationError, filterErrors, getErrorsForPath, partitionErrors, formatParameters};
