import {useBooleanState} from 'akeneo-design-system';
import {useRoute} from '@akeneo-pim-community/shared';

const useCheckInstanceCanBeReset = () => {
  const route = useRoute('akeneo_installer_check_reset_instance');
  const [isLoading, startLoading, stopLoading] = useBooleanState(false);

  const checkInstanceCanBeReset = async () => {
    startLoading();
    const response = await fetch(route, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });

    stopLoading();
    if (response.ok) {
      return;
    }

    throw Error(response.statusText);
  };

  return [isLoading, checkInstanceCanBeReset] as const;
};

export {useCheckInstanceCanBeReset};
