import {RuleDefinition} from './RuleDefinition';
import {Router} from '../dependenciesTools';
import {getAttributesByIdentifiers} from '../repositories/AttributeRepository';

const extractFieldIdentifiers = (json: any): string[] => {
  const indexedFieldIdentifiers: {[identifier: string]: boolean} = {};

  const conditions = json.content?.conditions ?? [];
  if (Array.isArray(conditions)) {
    conditions.forEach((condition: any) => {
      if ('string' === typeof condition.field) {
        indexedFieldIdentifiers[condition.field] = true;
      }
    });
  }

  const actions = json.content?.actions || [];
  if (Array.isArray(actions)) {
    actions.forEach((action: any) => {
      if ('string' === typeof action.field) {
        indexedFieldIdentifiers[action.field] = true;
      }
    });
  }

  return Object.keys(indexedFieldIdentifiers);
};

const prepareCacheAttributes = async (
  json: any,
  router: Router
): Promise<void> => {
  const fieldIdentifiers = extractFieldIdentifiers(json);
  await getAttributesByIdentifiers(fieldIdentifiers, router);
};

const denormalize = async function(
  json: any,
  router: Router
): Promise<RuleDefinition> {
  if (
    typeof json.id !== 'number' ||
    typeof json.code !== 'string' ||
    (typeof json.labels !== 'undefined' && typeof json.labels !== 'object') ||
    (typeof json.priority !== 'undefined' && typeof json.priority !== 'number')
  ) {
    throw new Error('Unable to parse rule definition ' + JSON.stringify(json));
  }
  const id = json.id;
  const code = json.code;
  const labels = json.labels || {};
  const priority = json.priority || 0;
  const enabled = json.enabled;

  await prepareCacheAttributes(json, router);

  return {
    id,
    code,
    labels,
    priority,
    enabled,
    conditions: json.content?.conditions || [],
    actions: json.content?.actions || [],
  };
};

export {denormalize};
