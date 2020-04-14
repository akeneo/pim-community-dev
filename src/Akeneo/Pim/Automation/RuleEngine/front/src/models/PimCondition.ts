/**
 * A PimCondition is a condition but not coded for now.
 * Its difference with the fallback is that it can be have its renderer.
 * Each condition has the same fields.
 */
type PimCondition = {
  type: 'PimCondition',
  field: string;
  operator: string;
  value: any|null;
  locale: string|null;
  scope: string|null;
}

export const createPimCondition = (json: any) : PimCondition | null => {
  if (typeof json.field === 'string' &&
    typeof json.operator === 'string' // TODO check operator
  ) {
    return {
      type: 'PimCondition',
      field: json.field,
      operator: json.operator,
      value: json.value,
      locale: json.locale,
      scope: json.scope
    };
  }

  return null;
};

export { PimCondition }
