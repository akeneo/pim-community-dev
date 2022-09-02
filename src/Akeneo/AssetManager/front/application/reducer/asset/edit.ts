import user, {UserState} from 'akeneoassetmanager/application/reducer/user';
import right, {RightState} from 'akeneoassetmanager/application/reducer/right';
import form, {EditionFormState} from 'akeneoassetmanager/application/reducer/asset/edit/form';
import structure, {StructureState} from 'akeneoassetmanager/application/reducer/structure';

export interface EditState {
  user: UserState;
  right: RightState;
  form: EditionFormState;
  structure: StructureState;
}

export default {
  user,
  right,
  structure,
  form,
};
