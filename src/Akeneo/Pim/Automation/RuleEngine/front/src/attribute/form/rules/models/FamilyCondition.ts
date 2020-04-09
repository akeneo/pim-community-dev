// src/Akeneo/Pim/Enrichment/Component/Product/Query/Filter/Operators.php
enum FamilyOperator {
  IS_EMPTY = 'EMPTY',
  IS_NOT_EMPTY = 'NOT EMPTY',
  IN_LIST = 'IN',
  NOT_IN_LIST = 'NOT IN',
}

export type FamilyCondition = {
  operator: FamilyOperator;
  familyCodes: string[];
}

export const createFamilyCondition = (json: any): FamilyCondition | false => {
  // TODO Remove this line when we implement family condition.
  return false;

  if (json.field !== 'family') {
    return false;
  }
  if (![FamilyOperator.IS_EMPTY, FamilyOperator.IS_NOT_EMPTY, FamilyOperator.IN_LIST, FamilyOperator.NOT_IN_LIST].includes(json.operator)) {
    return false;
  }

  return {
    operator: json.operator,
    familyCodes: json.value
  };
};
