import {useState, useEffect} from 'react';
import {useIsMounted, useRoute} from '@akeneo-pim-community/shared';
import {ReferenceEntityAttribute} from '../models';

const useReferenceEntityAttributes = (referenceEntityCode: string) => {
  const [referenceEntityAttributes, setReferenceEntityAttributes] = useState<ReferenceEntityAttribute[]>([]);
  const isMounted = useIsMounted();
  const route = useRoute('pimee_tailored_export_get_reference_entity_attributes_action', {
    reference_entity_code: referenceEntityCode,
  });

  useEffect(() => {
    const fetchReferenceEntityAttributes = async () => {
      const response = await fetch(route, {
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      const result = await response.json();

      if (!isMounted()) return;

      setReferenceEntityAttributes(result);
    };

    void fetchReferenceEntityAttributes();
  }, [isMounted, route]);

  return referenceEntityAttributes;
};

export {useReferenceEntityAttributes};
