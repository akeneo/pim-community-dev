import { RuleDefinition } from './RuleDefinition';
import { Condition, ConditionFactoryType } from './Condition';
import { createFamilyCondition } from './FamilyCondition';
import { createFallbackCondition } from './FallbackCondition';
import { createFallbackAction, FallbackAction } from './FallbackAction';
import { Action } from './Action';
import { createPimCondition } from './PimCondition';
import { createTextAttributeCondition } from './TextAttributeCondition';
import { Router } from '../dependenciesTools';
import { getAttributesByIdentifiers } from '../repositories/AttributeRepository';

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
  const factories: ConditionFactoryType[] = [
    createFamilyCondition,
    createTextAttributeCondition,
    createPimCondition,
  ];

  for (let i = 0; i < factories.length; i++) {
    const factory = factories[i];
    const condition = await factory(jsonCondition, router);
    if (condition !== null) {
      return condition;
    }
  }

  return createFallbackCondition(jsonCondition);
}

const extractFieldIdentifiers = (json: any): string[] => {
  if (
    'undefined' === typeof json.content ||
    'undefined' === typeof json.content.conditions ||
    !Array.isArray(json.content.conditions)
  ) {
    return [];
  }

  const indexedFieldIdentifiers: { [identifier: string]: boolean } = {};
  json.content.conditions.forEach((condition: any) => {
    if ('string' === typeof condition.field) {
      indexedFieldIdentifiers[condition.field] = true;
    }
  });

  return Object.keys(indexedFieldIdentifiers);
};

const prepareCacheAttributes = async (
  json: any,
  router: Router
): Promise<void> => {
  const fieldIdentifiers = extractFieldIdentifiers(json);
  await getAttributesByIdentifiers(fieldIdentifiers, router);
};

export const denormalize = async function(
  json: any,
  router: Router
): Promise<RuleDefinition> {
  const code = json.code;
  const labels = json.labels;
  const priority = json.priority;
  let actions: FallbackAction[] = [];
  let conditions: Condition[] = [];

  await prepareCacheAttributes(json, router);

  if (Array.isArray(json.content.actions)) {
    actions = json.content.actions.map((jsonAction: any) => {
      return denormalizeAction(jsonAction);
    });
  }

  if (Array.isArray(json.content.conditions)) {
    conditions = (await Promise.all(
      json.content.conditions.map(async (jsonCondition: any) => {
        return await denormalizeCondition(jsonCondition, router);
      })
    )) as Condition[];
  }

  return {
    code: code,
    labels: labels,
    priority: priority,
    conditions: conditions,
    actions: actions,
  };
};
