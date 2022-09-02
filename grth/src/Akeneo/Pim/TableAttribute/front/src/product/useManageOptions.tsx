import React from 'react';
import {ManageOptionsModal as OriginalManageOptionsModal} from '../attribute';
import {castSelectColumnDefinition, ColumnCode, SelectOption} from '../models';
import {useAttributeContext} from '../contexts';
import {useBooleanState} from 'akeneo-design-system';
import {SelectOptionRepository} from '../repositories';
import {NotificationLevel, useNotify, useRouter, useTranslate} from '@akeneo-pim-community/shared';

const useManageOptions = (columnCode: ColumnCode) => {
  const router = useRouter();
  const notify = useNotify();
  const translate = useTranslate();
  const {attribute, setAttribute} = useAttributeContext();
  const [isManageOptionsOpen, openManageOptions, closeManageOptions] = useBooleanState(false);

  const handleSaveOptions = (selectOptions: SelectOption[]) => {
    if (attribute) {
      SelectOptionRepository.save(router, attribute, columnCode, selectOptions).then(result => {
        if (result) {
          setAttribute({...attribute});
          notify(NotificationLevel.SUCCESS, translate('pim_table_attribute.form.product.save_options_success'));
        } else {
          /* istanbul ignore next */
          notify(NotificationLevel.ERROR, translate('pim_table_attribute.form.product.save_options_error'));
        }
      });
    }
  };

  const column = attribute?.table_configuration.find(({code}) => code === columnCode);

  const ManageOptionsModal = () => {
    return (
      <>
        {attribute && isManageOptionsOpen && column && (
          <OriginalManageOptionsModal
            onClose={closeManageOptions}
            attribute={attribute}
            columnDefinition={castSelectColumnDefinition(column)}
            onChange={handleSaveOptions}
            confirmLabel={translate('pim_common.save')}
          />
        )}
      </>
    );
  };

  return {
    ManageOptionsModal,
    openManageOptions,
  };
};

export {useManageOptions};
