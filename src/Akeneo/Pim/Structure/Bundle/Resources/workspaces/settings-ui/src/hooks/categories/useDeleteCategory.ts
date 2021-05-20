import {NotificationLevel, useNotify, useTranslate} from '@akeneo-pim-community/shared';
import {deleteCategory} from '../../infrastructure/removers';

const MAX_NUMBER_OF_PRODUCTS_TO_ALLOW_DELETE = 100;

type CategoryToDelete = {
  identifier: number;
  label: string;
  onDelete: () => void;
};

const useDeleteCategory = () => {
  const translate = useTranslate();
  const notify = useNotify();

  const isCategoryDeletionPossible = (
    identifier: number,
    label: string,
    numberOfProducts: number,
  ): boolean => {
    if (numberOfProducts > MAX_NUMBER_OF_PRODUCTS_TO_ALLOW_DELETE) {
      notify(
        NotificationLevel.INFO,
        translate('pim_enrich.entity.category.category_deletion.products_limit_exceeded.title'),
        translate('pim_enrich.entity.category.category_deletion.products_limit_exceeded.message', {
          name: label,
          limit: MAX_NUMBER_OF_PRODUCTS_TO_ALLOW_DELETE,
        })
      );

      return false;
    }

    return true;
  };

  const handleDeleteCategory = async (categoryToDelete: CategoryToDelete) => {
    const success = await deleteCategory(categoryToDelete.identifier);
    success && categoryToDelete.onDelete();

    const message = success
      ? 'pim_enrich.entity.category.category_deletion.success'
      : 'pim_enrich.entity.category.category_deletion.error';

    notify(
      success ? NotificationLevel.SUCCESS : NotificationLevel.ERROR,
      translate(message, {name: categoryToDelete.label})
    );
  };

  return {
    isCategoryDeletionPossible,
    handleDeleteCategory,
  }
}

export {useDeleteCategory, CategoryToDelete};
