import user, {UserState} from 'akeneoassetmanager/application/reducer/user';
import right, {RightState} from 'akeneoassetmanager/application/reducer/right';
import form, {EditionFormState} from 'akeneoassetmanager/application/reducer/asset-family/edit/form';
import createAttribute, {
  CreateState as CreateAttributeState,
} from 'akeneoassetmanager/application/reducer/attribute/create';
import structure, {StructureState} from 'akeneoassetmanager/application/reducer/structure';
import permission, {PermissionState} from 'akeneoassetmanager/application/reducer/asset-family/edit/permission';
import attributes, {ListState} from 'akeneoassetmanager/application/reducer/attribute/list';
import attribute, {EditState as EditAttributeState} from 'akeneoassetmanager/application/reducer/attribute/edit';
import {
  editOptionsReducer as options,
  EditOptionState,
} from 'akeneoassetmanager/application/reducer/attribute/type/option';

export interface EditState {
  user: UserState;
  right: RightState;
  createAttribute: CreateAttributeState;
  attributes: ListState;
  attribute: EditAttributeState;
  options: EditOptionState;
  form: EditionFormState;
  assetCount: number;
  structure: StructureState;
  permission: PermissionState;
}

export default {
  user,
  right,
  createAttribute,
  attributes,
  attribute,
  structure,
  permission,
  options,
  form,
};
