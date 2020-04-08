import RuleDefinition from "./RuleDefinition";
import Action from "./Action";
import Condition from "./Condition";
import FallbackAction from "./FallbackAction";
import FallbackCondition from "./FallbackCondition";
import FamilyCondition from "./FamilyCondition";

function denormalizeAction(jsonAction: any): Action {
  // For now, it only parse fallbacks.
  return new FallbackAction(jsonAction);
}

function denormalizeCondition(jsonCondition: any): Condition {
  // For now, FamilyCondition never match. It always returns FallbackCondition.
  const conditionClasses = [FamilyCondition];
  for (let j = 0; j < conditionClasses.length; j++) {
    const buildedCondition = conditionClasses[j].match(jsonCondition);
    if (buildedCondition) {
      return buildedCondition;
    }
  }

  return new FallbackCondition(jsonCondition);
}

export const denormalize = function(json: any): RuleDefinition {
  const code = json.code || '';
  const labels = json.labels || {};
  const actions = (json.content.actions || []).map((jsonAction: any) => {
    return denormalizeAction(jsonAction)
  });
  const conditions = (json.content.conditions || []).map((jsonCondition: any) => {
    return denormalizeCondition(jsonCondition)
  });
  const priority = json.priority || 0;

  return new RuleDefinition(
    code,
    labels,
    priority,
    conditions,
    actions
  );
};
