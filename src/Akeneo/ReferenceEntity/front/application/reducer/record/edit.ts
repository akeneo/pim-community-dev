import user, {UserState} from 'akeneoreferenceentity/application/reducer/user';
import sidebar, {SidebarState} from 'akeneoreferenceentity/application/reducer/sidebar';
import form, {EditionFormState} from 'akeneoreferenceentity/application/reducer/record/edit/form';
import structure, {StructureState} from 'akeneoreferenceentity/application/reducer/structure';
import confirmDelete, {ConfirmDeleteState} from 'akeneoreferenceentity/application/reducer/confirmDelete';

export interface EditState {
  user: UserState;
  sidebar: SidebarState;
  form: EditionFormState;
  structure: StructureState;
  confirmDelete: ConfirmDeleteState;
}

export default {
  user,
  sidebar,
  structure,
  form,
  confirmDelete,
};
