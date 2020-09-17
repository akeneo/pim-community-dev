import React from 'react';
import { ActionLineProps } from '../../pages/EditRules/components/actions/ActionLineProps';
import {
  getAddActionModule,
  getAddAssociationsActionModule,
  getAddAttributeValueActionModule,
  getAddCategoriesModule,
  getAddGroupsActionModule,
  getCalculateActionModule,
  getClearAssociationsActionModule,
  getClearAttributeActionModule,
  getClearCategoriesActionModule,
  getClearGroupsActionModule,
  getClearQuantifiedAssociationsActionModule,
  getConcatenateActionModule,
  getCopyActionModule,
  getRemoveAttributeValueActionModule,
  getRemoveCategoriesModule,
  getRemoveGroupsActionModule,
  getSetActionModule,
  getSetAssociationsActionModule,
  getSetCategoriesModule,
  getSetFamilyActionModule,
  getSetGroupsActionModule,
  getSetQuantifiedAssociationsActionModule,
  getSetStatusActionModule,
} from './';
import { FallbackActionLine } from '../../pages/EditRules/components/actions/FallbackActionLine';
import { Router } from '../../dependenciesTools';

export type ActionModuleGuesser = (
  json: any,
  router: Router
) => Promise<React.FC<ActionLineProps> | null>;

const getActionModule: (
  json: any,
  router: Router
) => Promise<React.FC<ActionLineProps>> = async (json, router) => {
  const getActionModuleFunctions: ActionModuleGuesser[] = [
    getAddCategoriesModule,
    getAddAssociationsActionModule,
    getAddGroupsActionModule,
    getClearAssociationsActionModule,
    getClearCategoriesActionModule,
    getClearGroupsActionModule,
    getClearQuantifiedAssociationsActionModule,
    getRemoveCategoriesModule,
    getRemoveGroupsActionModule,
    getSetAssociationsActionModule,
    getSetCategoriesModule,
    getSetFamilyActionModule,
    getSetGroupsActionModule,
    getSetQuantifiedAssociationsActionModule,
    getSetStatusActionModule,
    getCalculateActionModule,
    getConcatenateActionModule,
    getCopyActionModule,
    // Attribute values: they should be tested after the system field actions since their 'field' is dynamic
    getAddAttributeValueActionModule,
    getClearAttributeActionModule,
    getRemoveAttributeValueActionModule,
    // Fallbacks
    getAddActionModule,
    getSetActionModule,
  ];

  for (let i = 0; i < getActionModuleFunctions.length; i++) {
    const getModuleFunction = getActionModuleFunctions[i];
    const module = await getModuleFunction(json, router);
    if (module !== null) {
      return module;
    }
  }

  return FallbackActionLine;
};

export { getActionModule };
