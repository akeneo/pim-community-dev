import user, {UserState} from 'akeneoenrichedentity/application/reducer/user';
import sidebar, {SidebarState} from 'akeneoenrichedentity/application/reducer/sidebar';
import grid, {GridState} from 'akeneoenrichedentity/application/reducer/grid';
import createRecord, {CreateState as CreateRecordState} from 'akeneoenrichedentity/application/reducer/record/create';
import form, {EditionFormState} from 'akeneoenrichedentity/application/reducer/enriched-entity/edit/form';
import Record from 'akeneoenrichedentity/domain/model/record/record';
import createAttribute, {
  CreateState as CreateAttributeState,
} from 'akeneoenrichedentity/application/reducer/attribute/create';
import structure, {StructureState} from 'akeneoenrichedentity/application/reducer/structure';
import attributes, {ListState} from 'akeneoenrichedentity/application/reducer/attribute/list';
import attribute, {EditState as EditAttributeState} from 'akeneoenrichedentity/application/reducer/attribute/edit';

export interface EditState {
  user: UserState;
  sidebar: SidebarState;
  grid: GridState<Record>;
  createRecord: CreateRecordState;
  createAttribute: CreateAttributeState;
  attributes: ListState;
  attribute: EditAttributeState;
  form: EditionFormState;
  structure: StructureState;
}

export default {
  user,
  sidebar,
  grid,
  createRecord,
  createAttribute,
  attributes,
  attribute,
  structure,
  form,
};
