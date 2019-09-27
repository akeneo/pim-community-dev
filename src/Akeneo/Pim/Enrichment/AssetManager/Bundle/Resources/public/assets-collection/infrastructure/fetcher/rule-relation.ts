import promisify from 'akeneoassetmanager/tools/promisify';
import {RuleRelation} from 'akeneopimenrichmentassetmanager/platform/model/structure/rule-relation';
import {isString, isArray} from 'akeneoassetmanager/domain/model/utils';
const ruleManager = require('pimee/rule-manager');

export const fetchRuleRelations = async (): Promise<RuleRelation[]> => {
  const ruleRelations = await promisify(ruleManager.getRuleRelations('attribute'));

  return denormalizeRuleRelationCollection(ruleRelations);
};

const denormalizeRuleRelationCollection = (ruleRelations: any): RuleRelation[] => {
  if (!isArray(ruleRelations)) {
    throw Error('not a valid channel collection');
  }

  return ruleRelations.map((ruleRelation: any) => denormalizeRuleRelation(ruleRelation));
};

const denormalizeRuleRelation = (ruleRelation: any): RuleRelation => {
  if (!isString(ruleRelation.attribute)) {
    throw Error('The attribute is not well formated');
  }

  if (!isString(ruleRelation.rule)) {
    throw Error('The rule is not well formated');
  }

  return ruleRelation;
};
