import user, {UserState} from 'akeneoassetmanager/application/reducer/user';
import right, {RightState} from 'akeneoassetmanager/application/reducer/right';
import sidebar, {SidebarState} from 'akeneoassetmanager/application/reducer/sidebar';
import form, {EditionFormState} from 'akeneoassetmanager/application/reducer/asset/edit/form';
import products, {ProductsState} from 'akeneoassetmanager/application/reducer/asset/edit/products';
import structure, {StructureState} from 'akeneoassetmanager/application/reducer/structure';
import confirmDelete, {ConfirmDeleteState} from 'akeneoassetmanager/application/reducer/confirmDelete';

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
