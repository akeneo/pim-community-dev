import { Condition } from './conditions';
import { Action } from './Action';

export type RuleDefinition = {
  id: number;
  code: string;
  labels: { [localeCode: string]: string };
  priority: number;
  enabled: boolean;
  conditions: Condition[];
  actions: Action[];
};
