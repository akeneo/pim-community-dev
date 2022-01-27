import React from 'react';
import {useRouter, useUserContext} from '@akeneo-pim-community/shared';
import {AttributeCode} from '../models';

type AttributeWithOptions = {
  code: AttributeCode;
  label: string;
  options_count: number;
};

const useAttributeWithOptions: (isOpen: boolean) => AttributeWithOptions[] = isOpen => {
  const router = useRouter();
  const userContext = useUserContext();
  const locale = userContext.get('catalogLocale');
  const [attributes, setAttributes] = React.useState<AttributeWithOptions[] | undefined>();

  React.useEffect(() => {
    if (isOpen && !attributes) {
      const url = router.generate('pim_table_attribute_get_select_attributes_with_options_count', {locale});
      fetch(url, {
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      }).then(response => {
        response.json().then(js => {
          setAttributes(js as AttributeWithOptions[]);
        });
      });
    }
  }, [isOpen]);

  return attributes || [];
};

export {useAttributeWithOptions};
