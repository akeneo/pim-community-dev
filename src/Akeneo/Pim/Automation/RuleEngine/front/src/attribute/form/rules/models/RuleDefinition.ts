import Condition from "./Condition";
import Action from "./Action";

export default class RuleDefinition {
  public code: string;
  public labels: { [localeCode: string]: string; };
  public priority: number;
  public conditions: Condition[];
  public actions: Action[];

  constructor(
    code: string,
    labels: { [localeCode: string]: string; },
    priority: number,
    conditions: Condition[],
    actions: Action[],
  ) {
    this.code = code;
    this.labels = labels;
    this.priority = priority;
    this.conditions = conditions;
    this.actions = actions;
  }

  getLabel(locale: string) {
    return this.labels[locale] || '[' + this.code + ']';
  }
}
