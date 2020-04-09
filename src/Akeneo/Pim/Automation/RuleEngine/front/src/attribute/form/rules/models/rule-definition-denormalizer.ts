import {RuleDefinition} from "./RuleDefinition";
import {Condition} from "./Condition";
import {createFamilyCondition} from "./FamilyCondition";
import {createFallbackCondition} from "./FallbackCondition";
import {createFallbackAction} from "./FallbackAction";
import {Action} from "./Action";

function denormalizeAction(jsonAction: any): Action {
  const factories: ((json: any) => Action | null)[] = [];
  const factory = factories.find((factory) => {
    return factory(jsonAction) !== null;
  }) || createFallbackAction;

  return <Action> factory(jsonAction);
}

function denormalizeCondition(jsonCondition: any): Condition {
  // For now, FamilyCondition never match. It always returns FallbackCondition.
  const factories: ((json: any) => Condition | null)[] = [createFamilyCondition];
  const factory = factories.find((factory) => {
    return factory(jsonCondition) !== null;
  }) || createFallbackCondition;

  return <Condition> factory(jsonCondition);
}

export const denormalize = function(json: any): RuleDefinition {
  const code = json.code;
  const labels = json.labels;
  const actions = json.content.actions.map((jsonAction: any) => {
    return denormalizeAction(jsonAction)
  });
  const conditions = json.content.conditions.map((jsonCondition: any) => {
    return denormalizeCondition(jsonCondition)
  });
  const priority = json.priority;

  return {
    code: code,
    labels: labels,
    priority: priority,
    conditions: conditions,
    actions: actions
  };
};
