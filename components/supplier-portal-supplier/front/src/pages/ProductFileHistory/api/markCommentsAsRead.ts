import {apiFetch} from '../../../api/apiFetch';

const markCommentsAsRead = async (productFileIdentifier: string): Promise<void> => {
    await apiFetch(`/supplier-portal/product-file/${productFileIdentifier}/markCommentsAsRead`, {
        method: 'POST',
        headers: [
            ['Content-type', 'application/json'],
            ['X-Requested-With', 'XMLHttpRequest'],
        ],
    });
};

export {markCommentsAsRead};
