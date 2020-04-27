import { Condition } from './Condition';
import { Action } from './Action';

export type RuleDefinition = {
  code: string;
  labels: { [localeCode: string]: string };
  priority: number;
  conditions: Condition[];
  actions: Action[];
};

export const getRuleDefinitionLabel = (
  ruleDefinition: RuleDefinition,
  locale: string
) => {
  return ruleDefinition.labels[locale] || `[${ruleDefinition.code}]`;
};
