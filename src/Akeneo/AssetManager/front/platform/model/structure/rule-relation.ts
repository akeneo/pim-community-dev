import {AttributeCode} from 'akeneoassetmanager/platform/model/structure/attribute';

export type RulesNumberByAttribute = {
  [attributeCode: string]: number;
};

export const getRulesForAttribute = (
  attributeCode: AttributeCode,
  rulesNumberByAttribute: RulesNumberByAttribute
): number => {
  return Object.keys(rulesNumberByAttribute).includes(attributeCode) ? rulesNumberByAttribute[attributeCode] : 0;
};
