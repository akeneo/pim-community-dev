import {Router} from '@akeneo-pim-community/shared';
import {CategoryTreeModel} from '../../models';

const createTemplate = async (categoryTree: CategoryTreeModel, router: Router) => {
    const data = {
        'code': categoryTree.code + '_template',
        'labels': categoryTree.label + ' template'
    };

    const url = router.generate('pim_category_template_rest_create', {
        templateCode: categoryTree.code + '_template',
        categoryTreeId: categoryTree.id
    });

    const response = await fetch(url, {
        method: 'POST',
        body: JSON.stringify(data),
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            Accept: 'application/json',
        },
    });

    if (!response.ok){
        return await response.json();
    }
    return {};
}

export {createTemplate};
