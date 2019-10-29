/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {AttributeGroup} from '../../../domain/model/attribute-group';
import {search} from '../../../infrastructure/fetcher/attribute-group';

export function fetchAttributeGroups(attributeGroupCodes: string[]) {
  return async (dispatch: any) => {
    try {
      const attributeGroups = await search(attributeGroupCodes);
      dispatch(fetchAttributeGroupsSuccess(attributeGroups));
    } catch {
      dispatch(fetchAttributeGroupsFail());
    }
  };
}

export const FETCH_ATTRIBUTE_GROUPS_SUCCESS = 'FETCH_ATTRIBUTE_GROUPS_SUCCESS';

export interface FetchAttributeGroupsSuccessAction {
  type: typeof FETCH_ATTRIBUTE_GROUPS_SUCCESS;
  attributeGroups: {
    [attributeGroupCode: string]: AttributeGroup;
  };
}

export function fetchAttributeGroupsSuccess(attributeGroups: {
  [attributeGroupCode: string]: AttributeGroup;
}): FetchAttributeGroupsSuccessAction {
  return {
    type: FETCH_ATTRIBUTE_GROUPS_SUCCESS,
    attributeGroups
  };
}

export const FETCH_ATTRIBUTE_GROUPS_FAIL = 'FETCH_ATTRIBUTE_GROUPS_FAIL';

export interface FetchAttributeGroupsFailAction {
  type: typeof FETCH_ATTRIBUTE_GROUPS_FAIL;
}

export function fetchAttributeGroupsFail(): FetchAttributeGroupsFailAction {
  return {
    type: FETCH_ATTRIBUTE_GROUPS_FAIL
  };
}

export type AttributeGroupsActions = FetchAttributeGroupsSuccessAction | FetchAttributeGroupsFailAction;
