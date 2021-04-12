import user, {UserState} from 'akeneoreferenceentity/application/reducer/user';
import right, {RightState} from 'akeneoreferenceentity/application/reducer/right';
import sidebar, {SidebarState} from 'akeneoreferenceentity/application/reducer/sidebar';
import grid, {GridState} from 'akeneoreferenceentity/application/reducer/grid';
import createRecord, {CreateState as CreateRecordState} from 'akeneoreferenceentity/application/reducer/record/create';
import form, {EditionFormState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit/form';
import {NormalizedItemRecord} from 'akeneoreferenceentity/domain/model/record/record';
import createAttribute, {
  CreateState as CreateAttributeState,
} from 'akeneoreferenceentity/application/reducer/attribute/create';
import structure, {StructureState} from 'akeneoreferenceentity/application/reducer/structure';
import permission, {PermissionState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit/permission';
import attributes, {ListState} from 'akeneoreferenceentity/application/reducer/attribute/list';
import attribute, {EditState as EditAttributeState} from 'akeneoreferenceentity/application/reducer/attribute/edit';
import {
  editOptionsReducer as options,
  EditOptionState,
} from 'akeneoreferenceentity/application/reducer/attribute/type/option';
import confirmDelete, {ConfirmDeleteState} from 'akeneoreferenceentity/application/reducer/confirmDelete';

export interface EditState {
  user: UserState;
  right: RightState;
  sidebar: SidebarState;
  grid: GridState<NormalizedItemRecord>;
  createRecord: CreateRecordState;
  createAttribute: CreateAttributeState;
  attributes: ListState;
  attribute: EditAttributeState;
  options: EditOptionState;
  form: EditionFormState;
  recordCount: number;
  structure: StructureState;
  permission: PermissionState;
  confirmDelete: ConfirmDeleteState;
}

export default {
  user,
  right,
  sidebar,
  grid,
  createRecord,
  createAttribute,
  attributes,
  attribute,
  structure,
  permission,
  options,
  form,
  confirmDelete,
};
