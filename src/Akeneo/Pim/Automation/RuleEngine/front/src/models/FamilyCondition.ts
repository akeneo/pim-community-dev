// src/Akeneo/Pim/Enrichment/Component/Product/Query/Filter/Operators.php
enum FamilyOperator {
  IS_EMPTY = 'EMPTY',
  IS_NOT_EMPTY = 'NOT EMPTY',
  IN_LIST = 'IN',
  NOT_IN_LIST = 'NOT IN',
}

type FamilyCondition = {
  type: 'FamilyCondition',
  operator: FamilyOperator;
  familyCodes: string[];
}

const createFamilyCondition = (json: any): FamilyCondition | null => {
  // TODO Remove this line when we implement family condition.
  return null;

  if (json.field !== 'family') {
    return null;
  }
  if (![FamilyOperator.IS_EMPTY, FamilyOperator.IS_NOT_EMPTY, FamilyOperator.IN_LIST, FamilyOperator.NOT_IN_LIST].includes(json.operator)) {
    return null;
  }

  return {
    type: 'FamilyCondition',
    operator: json.operator,
    familyCodes: json.value
  };
};

export { FamilyCondition, createFamilyCondition }
