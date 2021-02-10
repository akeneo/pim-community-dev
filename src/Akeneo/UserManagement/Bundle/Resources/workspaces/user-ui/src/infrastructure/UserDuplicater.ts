import {Router} from '@akeneo-pim-community/legacy-bridge';

const duplicateUser = async (router: Router, baseUserId: number, data: any): Promise<Response | null> => {
  const url = router.generate('pim_user_user_rest_duplicate', {identifier: baseUserId});
  try {
    return await fetch(url, {
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      method: 'POST',
      body: JSON.stringify(data),
    });
  } catch (error) {
    console.error(error);
  }
  return null;
};

export {duplicateUser};
