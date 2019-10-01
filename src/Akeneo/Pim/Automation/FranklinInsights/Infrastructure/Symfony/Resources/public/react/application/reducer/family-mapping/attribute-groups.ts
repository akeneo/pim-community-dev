/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {createReducer} from '../../../infrastructure/create-reducer';
import {AttributeGroup} from '../../../domain/model/attribute-group';
import {
  FETCH_ATTRIBUTE_GROUPS_SUCCESS,
  AttributeGroupsActions,
  FetchAttributeGroupsSuccessAction,
  FETCH_ATTRIBUTE_GROUPS_FAIL
} from '../../action/family-mapping/attribute-group';

export interface AttributeGroupsState {
  [attributeGroupCode: string]: AttributeGroup;
}

const fetchAttributeGroupsSuccess = (_: AttributeGroupsState, action: FetchAttributeGroupsSuccessAction) =>
  action.attributeGroups;

const fetchAttributeGroupsFail = () => ({});

const initialState: AttributeGroupsState = {};

export default createReducer<AttributeGroupsState, AttributeGroupsActions>(initialState, {
  [FETCH_ATTRIBUTE_GROUPS_SUCCESS]: fetchAttributeGroupsSuccess,
  [FETCH_ATTRIBUTE_GROUPS_FAIL]: fetchAttributeGroupsFail
});
