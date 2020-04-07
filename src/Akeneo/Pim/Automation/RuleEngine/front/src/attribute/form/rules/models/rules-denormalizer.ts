import Rule from "./Rule";
import Action from "./Action";
import Condition from "./Condition";
import FallbackAction from "./FallbackAction";
import FallbackCondition from "./FallbackCondition";

export const denormalize = function(json: any): Rule {
  function parseAction(jsonAction: any): Action {
    // For now, it only parse fallbacks.
    return new FallbackAction(jsonAction);
  }

  function parseCondition(jsonCondition: any): Condition {
    // For now, it only parse fallbacks.
    return new FallbackCondition(jsonCondition);
  }

  const code = json.code || '';
  const labels = json.labels || {};
  const actions = (json.content.actions || []).map((jsonAction: any) => {
    return parseAction(jsonAction)
  });
  const conditions = (json.content.conditions || []).map((jsonCondition: any) => {
    return parseCondition(jsonCondition)
  });
  const priority = json.priority || 0;

  return new Rule(
    code,
    labels,
    priority,
    actions,
    conditions
  );
};
