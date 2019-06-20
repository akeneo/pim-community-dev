import user, {UserState} from 'akeneoreferenceentity/application/reducer/user';
import right, {RightState} from 'akeneoreferenceentity/application/reducer/right';
import sidebar, {SidebarState} from 'akeneoreferenceentity/application/reducer/sidebar';
import form, {EditionFormState} from 'akeneoreferenceentity/application/reducer/record/edit/form';
import products, {ProductsState} from 'akeneoreferenceentity/application/reducer/record/edit/products';
import structure, {StructureState} from 'akeneoreferenceentity/application/reducer/structure';
import confirmDelete, {ConfirmDeleteState} from 'akeneoreferenceentity/application/reducer/confirmDelete';

export interface EditState {
  user: UserState;
  right: RightState;
  sidebar: SidebarState;
  form: EditionFormState;
  structure: StructureState;
  products: ProductsState;
  confirmDelete: ConfirmDeleteState;
}

export default {
  user,
  right,
  sidebar,
  structure,
  form,
  products,
  confirmDelete,
};
