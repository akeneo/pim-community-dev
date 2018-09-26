import user, {UserState} from 'akeneoenrichedentity/application/reducer/user';
import sidebar, {SidebarState} from 'akeneoenrichedentity/application/reducer/sidebar';
import form, {EditionFormState} from 'akeneoenrichedentity/application/reducer/record/edit/form';
import structure, {StructureState} from 'akeneoenrichedentity/application/reducer/structure';

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
