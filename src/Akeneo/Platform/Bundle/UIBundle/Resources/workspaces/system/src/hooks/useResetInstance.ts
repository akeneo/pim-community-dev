import {useRoute} from "@akeneo-pim-community/shared";

const useResetInstance = () => {
    const route = useRoute('akeneo_installer_reset_instance');

    return async () => {
        const response = await fetch(route, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        });

        if (response.ok) {
            return;
        }

        throw Error('Cannot reset instance');
    };
};

export {useResetInstance};
