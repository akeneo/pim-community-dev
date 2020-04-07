import Condition from "./Condition";

// src/Akeneo/Pim/Enrichment/Component/Product/Query/Filter/Operators.php
enum FamilyOperator {
  IS_EMPTY = 'EMPTY',
  IS_NOT_EMPTY = 'NOT EMPTY',
  IN_LIST = 'IN',
  NOT_IN_LIST = 'NOT IN',
}

export default class FamilyCondition implements Condition {
  public operator: FamilyOperator;
  public familyCodes: string[];

  constructor(operator: FamilyOperator, familyCodes: string[]) {
    this.operator = operator;
    this.familyCodes = familyCodes;
  }

  static match(json: any): FamilyCondition | false {
    // TODO Remove this line when we implement family condition.
    return false;

    if (json.field !== 'family') {
      return false;
    }
    if (![FamilyOperator.IS_EMPTY, FamilyOperator.IS_NOT_EMPTY, FamilyOperator.IN_LIST, FamilyOperator.NOT_IN_LIST].includes(json.operator)) {
      return false;
    }

    return new FamilyCondition(json.operator, json.value);
  }

  public toJson(): any {
    return {
      'field': 'family',
      'operator': this.operator,
      'value': this.familyCodes,
    }
  }
}
