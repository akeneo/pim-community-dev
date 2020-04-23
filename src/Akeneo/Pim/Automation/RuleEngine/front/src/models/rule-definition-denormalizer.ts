import { RuleDefinition } from './RuleDefinition';
import { Condition } from './Condition';
import { createFamilyCondition } from './FamilyCondition';
import { createFallbackCondition } from './FallbackCondition';
import { createFallbackAction } from './FallbackAction';
import { Action } from './Action';
import { createPimCondition } from './PimCondition';
import { createTextAttributeCondition } from './TextAttributeCondition';
import { Router } from '../dependenciesTools';

function denormalizeAction(jsonAction: any): Action {
  const factories: ((json: any) => Action | null)[] = [];
  const factory =
    factories.find(factory => {
      return factory(jsonAction) !== null;
    }) || createFallbackAction;

  return factory(jsonAction) as Action;
}

async function denormalizeCondition(
  jsonCondition: any,
  router: Router
): Promise<Condition> {
  // For now, FamilyCondition never match. It always returns FallbackCondition.
  const factories: ((
    json: any,
    router?: Router
  ) => Promise<Condition | null>)[] = [
    createFamilyCondition,
    createTextAttributeCondition,
    createPimCondition,
  ];

  for (let index in factories) {
    let condition = await factories[index](jsonCondition, router);
    if (condition !== null) {
      return condition;
    }
  }
  return createFallbackCondition(jsonCondition);
}

export const denormalize = async function(
  json: any,
  router: Router
): Promise<RuleDefinition> {
  const code = json.code;
  const labels = json.labels;
  const priority = json.priority;
  const actions = json.content.actions.map((jsonAction: any) => {
    return denormalizeAction(jsonAction);
  });

  // TODO We should call "AttributeFetcher.getAttributesFromIdentifiers()" with every .field property of conditions
  //      to do less backend calls here.

  const conditions = (await Promise.all(
    json.content.conditions.map(async (jsonCondition: any) => {
      return await denormalizeCondition(jsonCondition, router);
    })
  )) as Condition[];

  return {
    code: code,
    labels: labels,
    priority: priority,
    conditions: conditions,
    actions: actions,
  };
};
