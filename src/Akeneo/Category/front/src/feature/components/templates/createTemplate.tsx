import {Router} from '@akeneo-pim-community/shared';
import {CategoryTreeModel, Template} from '../../models';

const createTemplate = async (categoryTree: CategoryTreeModel, router: Router) => {
    const data = {
        'code': categoryTree.code + '_template',
        'labels': [categoryTree.label + ' template']
    };

    const url = router.generate('pim_category_template_rest_create', {
        categoryTreeId: categoryTree.id
    });

    return fetch(url, {
        method: 'POST',
        body: JSON.stringify(data),
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            Accept: 'application/json',
        },
    });
}

export {createTemplate};
