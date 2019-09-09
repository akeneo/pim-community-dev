import {AttributeCode} from 'akeneopimenrichmentassetmanager/platform/model/structure/attribute';

export type RuleCode = string;
export type RuleRelation = {
  attribute: AttributeCode;
  rule: RuleCode;
};

export const isSmartAttribute = (attributeCode: AttributeCode, ruleRelations: RuleRelation[]): boolean => {
  const attributeIsInARuleRelation = ruleRelations.some(
    (ruleRelation: RuleRelation) => attributeCode === ruleRelation.attribute
  );

  return attributeIsInARuleRelation;
};

export const getRulesForAttribute = (attributeCode: AttributeCode, ruleRelations: RuleRelation[]): RuleCode[] => {
  const rulesForAttribute = ruleRelations.filter(
    (ruleRelation: RuleRelation) => attributeCode === ruleRelation.attribute
  );

  return rulesForAttribute.map((ruleRelation: RuleRelation) => ruleRelation.rule);
};
