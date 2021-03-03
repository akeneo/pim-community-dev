import promisify from 'akeneoassetmanager/tools/promisify';
import {RulesNumberByAttribute} from 'akeneoassetmanager/platform/model/structure/rule-relation';
const ruleManager = require('pimee/rule-manager');

export const fetchRuleRelations = async (attributeCodes: string[]): Promise<RulesNumberByAttribute> => {
  return await promisify(ruleManager.getFamilyAttributesRulesNumber(attributeCodes));
};
