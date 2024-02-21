import {AssociationType} from '@akeneo-pim-community/settings-ui';

const removeAssociationType = async (associationType: AssociationType) => {
  try {
    return fetch(associationType.deleteLink, {
      method: 'DELETE',
      headers: [['X-Requested-With', 'XMLHttpRequest']],
    }).then(response => {
      return response.ok;
    });
  } catch (error) {
    console.error(error);
    return false;
  }
};

export {removeAssociationType};
