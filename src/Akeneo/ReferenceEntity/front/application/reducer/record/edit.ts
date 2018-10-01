import user, {UserState} from 'akeneoreferenceentity/application/reducer/user';
import sidebar, {SidebarState} from 'akeneoreferenceentity/application/reducer/sidebar';
import form, {EditionFormState} from 'akeneoreferenceentity/application/reducer/record/edit/form';
import structure, {StructureState} from 'akeneoreferenceentity/application/reducer/structure';

export interface EditState {
  user: UserState;
  sidebar: SidebarState;
  form: EditionFormState;
  structure: StructureState;
}

export default {
  user,
  sidebar,
  structure,
  form,
};
