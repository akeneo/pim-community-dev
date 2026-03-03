import {useRoute} from '@akeneo-pim-community/shared';

const useCreateFamily = () => {
  const route = useRoute('pim_enrich_family_rest_create');

  return async (familyCode: string) => {
    const response = await fetch(route, {
      method: 'POST',
      headers: [
        ['Content-type', 'application/json'],
        ['X-Requested-With', 'XMLHttpRequest'],
      ],
      body: JSON.stringify({
        code: familyCode,
      }),
    });

    const decodedResponse = await response.json();

    return response.ok ? decodedResponse : Promise.reject(decodedResponse);
  };
};

export {useCreateFamily};
