import { Condition } from './Condition';
import { Action } from './Action';

export type RuleDefinition = {
  code: string;
  labels: { [localeCode: string]: string };
  priority: number;
  conditions: Condition[];
  actions: (Action | null)[];
};
