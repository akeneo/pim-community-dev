import {useEffect, useState} from 'react';
import {useSecurity} from '@akeneo-pim-community/shared';

enum AttributeGroupPermissions {
  Index = 'pim_enrich_attributegroup_index',
  Create = 'pim_enrich_attributegroup_create',
  Edit = 'pim_enrich_attributegroup_edit',
  Remove = 'pim_enrich_attributegroup_remove',
  Sort = 'pim_enrich_attributegroup_sort',
  AddAttribute = 'pim_enrich_attributegroup_add_attribute',
  RemoveAttribute = 'pim_enrich_attributegroup_remove_attribute',
  History = 'pim_enrich_attributegroup_history',
}

type AttributeGroupPermissionsState = {
  indexGranted: boolean;
  createGranted: boolean;
  editGranted: boolean;
  removeGranted: boolean;
  sortGranted: boolean;
  addAttributeGranted: boolean;
  removeAttributeGranted: boolean;
  historyGranted: boolean;
};

const useAttributeGroupPermissions = (): AttributeGroupPermissionsState => {
  const [state, setState] = useState<AttributeGroupPermissionsState>({
    indexGranted: false,
    createGranted: false,
    editGranted: false,
    removeGranted: false,
    sortGranted: false,
    addAttributeGranted: false,
    removeAttributeGranted: false,
    historyGranted: false,
  });
  const {isGranted} = useSecurity();

  useEffect(() => {
    if (typeof isGranted === 'function') {
      setState({
        indexGranted: isGranted(AttributeGroupPermissions.Index),
        createGranted: isGranted(AttributeGroupPermissions.Create),
        editGranted: isGranted(AttributeGroupPermissions.Edit),
        removeGranted: isGranted(AttributeGroupPermissions.Remove),
        sortGranted: isGranted(AttributeGroupPermissions.Sort),
        addAttributeGranted: isGranted(AttributeGroupPermissions.AddAttribute),
        removeAttributeGranted: isGranted(AttributeGroupPermissions.RemoveAttribute),
        historyGranted: isGranted(AttributeGroupPermissions.History),
      });
    }
  }, [isGranted]);

  return state;
};

export {useAttributeGroupPermissions};
