import {Category, EditableCategoryProperties} from '../../models';
import {useCallback, useEffect, useState} from "react";

const useEditCategory = (category: Category | null) => {
  const [editedProperties, setEditedProperties] = useState<EditableCategoryProperties | null>(null);
  const [thereAreUnsavedChanges, setThereAreUnsavedChanges] = useState<boolean>(false);

  const havePropertiesBeenChanged = useCallback((editedProperties: EditableCategoryProperties): boolean => {
    if (category === null) {
      return false;
    }

    const changedLabels = editedProperties.labels;
    for (const [locale, label] of Object.entries(category.labels)) {
      if (changedLabels.hasOwnProperty(locale) && changedLabels[locale] !== label) {
        return true;
      }
    }

    return false;
  }, [category]);

  const editProperties = useCallback((editedProperties: EditableCategoryProperties) => {
    setEditedProperties(editedProperties);
    setThereAreUnsavedChanges(havePropertiesBeenChanged(editedProperties));
  }, [category]);

  useEffect(() => {
    if (category === null) {
      return;
    }
    setEditedProperties({labels: category.labels});
  }, [category]);

  return {
    editedCategory: category === null ? null : editedProperties === null ? category : {...category, ...editedProperties},
    editProperties,
    thereAreUnsavedChanges
  };
};

export {useEditCategory}
