import { RuleDefinition } from './RuleDefinition';
import { ActionModuleGuesser } from './Action';
import { Router } from '../dependenciesTools';
import { getAttributesByIdentifiers } from '../repositories/AttributeRepository';
import {
  getAddActionModule,
  getCalculateActionModule,
  getClearActionModule,
  getConcatenateActionModule,
  getCopyActionModule,
  getRemoveActionModule,
  getSetActionModule,
  getSetFamilyActionModule,
} from './actions';
import {
  ConditionModuleGuesser,
  getFamilyConditionModule,
  getMultiOptionsAttributeConditionModule,
  getCategoryConditionModule,
  getPimConditionModule,
  getTextAttributeConditionModule
} from './conditions';
import React from "react";
import { ConditionLineProps } from "../pages/EditRules/components/conditions/ConditionLineProps";
import { FallbackConditionLine } from "../pages/EditRules/components/conditions/FallbackConditionLine";
import { ActionLineProps } from "../pages/EditRules/components/actions/ActionLineProps";
import { FallbackActionLine } from "../pages/EditRules/components/actions/FallbackActionLine";

const getActionModule: ((
  json: any,
) => Promise<React.FC<ActionLineProps>>) = async (json) => {
  const getModuleFunctions: ActionModuleGuesser[] = [
    getSetFamilyActionModule,
    getAddActionModule,
    getCalculateActionModule,
    getClearActionModule,
    getConcatenateActionModule,
    getCopyActionModule,
    getRemoveActionModule,
    getSetActionModule,
  ];

  for (let i = 0; i < getModuleFunctions.length; i++) {
    const getModuleFunction = getModuleFunctions[i];
    const module = await getModuleFunction(json);
    if (module !== null) {
      return module;
    }
  }

  return FallbackActionLine;
}

const getConditionModule: ((
  json: any,
  router: Router
) => Promise<React.FC<ConditionLineProps>>) = async (json, router) => {
  const getModuleFunctions: ConditionModuleGuesser[] = [
    getFamilyConditionModule,
    getCategoryConditionModule,
    getTextAttributeConditionModule,
    getMultiOptionsAttributeConditionModule,
    getPimConditionModule,
  ];

  for (let i = 0; i < getModuleFunctions.length; i++) {
    const getModuleFunction = getModuleFunctions[i];
    const module = await getModuleFunction(json, router);
    if (module !== null) {
      return module;
    }
  }

  return FallbackConditionLine;
}

const extractFieldIdentifiers = (json: any): string[] => {
  if (
    'undefined' === typeof json.content ||
    'undefined' === typeof json.content.conditions ||
    !Array.isArray(json.content.conditions)
  ) {
    return [];
  }

  // TODO Extract from actions too

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

  await prepareCacheAttributes(json, router);

  return {
    code: code,
    labels: labels,
    priority: priority,
    conditions: json.content?.conditions || [],
    actions: json.content?.actions || [],
  };
};

export { getConditionModule, getActionModule };
