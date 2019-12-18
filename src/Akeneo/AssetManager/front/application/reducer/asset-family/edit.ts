import user, {UserState} from 'akeneoassetmanager/application/reducer/user';
import right, {RightState} from 'akeneoassetmanager/application/reducer/right';
import sidebar, {SidebarState} from 'akeneoassetmanager/application/reducer/sidebar';
import grid, {GridState} from 'akeneoassetmanager/application/reducer/grid';
import createAsset, {CreateState as CreateAssetState} from 'akeneoassetmanager/application/reducer/asset/create';
import uploadAsset, {UploadState as UploadAssetState} from 'akeneoassetmanager/application/reducer/asset/upload';
import form, {EditionFormState} from 'akeneoassetmanager/application/reducer/asset-family/edit/form';
import {NormalizedAsset} from 'akeneoassetmanager/domain/model/asset/asset';
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
import confirmDelete, {ConfirmDeleteState} from 'akeneoassetmanager/application/reducer/confirmDelete';

export interface EditState {
  user: UserState;
  right: RightState;
  sidebar: SidebarState;
  grid: GridState<NormalizedAsset>;
  createAsset: CreateAssetState;
  uploadAsset: UploadAssetState;
  createAttribute: CreateAttributeState;
  attributes: ListState;
  attribute: EditAttributeState;
  options: EditOptionState;
  form: EditionFormState;
  assetCount: number;
  structure: StructureState;
  permission: PermissionState;
  confirmDelete: ConfirmDeleteState;
}

export default {
  user,
  right,
  sidebar,
  grid,
  createAsset,
  createAttribute,
  attributes,
  attribute,
  uploadAsset,
  structure,
  permission,
  options,
  form,
  confirmDelete,
};
