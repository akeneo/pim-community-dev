import * as React from 'react';
import {BooleanInput, Field} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {FC, useState} from 'react';

type Props = {
  configureBulkAction: (value: any) => void;
  defaultValue: any;
  readOnly: boolean;
};

const ChangeStatus: FC<Props> = ({configureBulkAction, defaultValue, readOnly}) => {
  const translate = useTranslate();
  const [value, setValue] = useState<boolean>(
    defaultValue && defaultValue.hasOwnProperty('value') ? defaultValue.value : false
  );

  const handleChange = (value: boolean) => {
    setValue(value);
    configureBulkAction([
      {
        field: 'enabled',
        value,
      },
    ]);
  };

  return (
    <Field label={translate('pim_enrich.mass_edit.product.operation.change_status.field')}>
      <BooleanInput
        value={value}
        readOnly={readOnly}
        yesLabel={translate('pim_common.yes')}
        noLabel={translate('pim_common.no')}
        onChange={handleChange}
      />
    </Field>
  );
};

export {ChangeStatus};
