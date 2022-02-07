import React from 'react';
import {useRouter, useUserContext} from '@akeneo-pim-community/shared';
import {AttributeWithOptions} from '../models';
import {AttributeFetcher} from '../fetchers';

const useAttributeWithOptions: (isOpen: boolean) => AttributeWithOptions[] = isOpen => {
  const router = useRouter();
  const userContext = useUserContext();
  const locale = userContext.get('catalogLocale');
  const [attributes, setAttributes] = React.useState<AttributeWithOptions[] | undefined>();

  React.useEffect(() => {
    AttributeFetcher.findAttributeWithOptions(router, locale).then(attributes => setAttributes(attributes));
  }, [isOpen]);

  return attributes || [];
};

export {useAttributeWithOptions};
