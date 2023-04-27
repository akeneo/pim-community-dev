import {useBooleanState} from "akeneo-design-system";
import {useRoute} from "@akeneo-pim-community/shared";

const useResetInstance = () => {
    const route = useRoute('akeneo_installer_reset_instance');
    const [isLoading, startLoading, stopLoading] = useBooleanState(false);

    const resetInstance = async () => {
        startLoading();
        const response = await fetch(route, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        });

        stopLoading();
        if (response.ok) {
            return;
        }

        throw Error('Cannot reset instance');
    };

    return [isLoading, resetInstance] as const;
};

export {useResetInstance};
