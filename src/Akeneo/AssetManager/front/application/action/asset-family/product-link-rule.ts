import productLinkRulesExecutor from 'akeneoassetmanager/infrastructure/product-link-rules-executor';
import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import {
    notifyExecuteProductLinkRulesFailed,
    notifyExecuteProductLinkRulesSucceeded,
} from 'akeneoassetmanager/application/action/asset-family/notify';

export const executeProductLinkRules = () => async (dispatch: any, getState: () => EditState): Promise<void> => {
    const assetFamily = getState().form.data;

    try {
        const errors = await productLinkRulesExecutor.execute(assetFamily);

        if (errors) {
            console.error(errors);
            dispatch(notifyExecuteProductLinkRulesFailed());
            return;
        }
    } catch (error) {

        console.error(error);
        dispatch(notifyExecuteProductLinkRulesFailed());

        return;
    }

    dispatch(notifyExecuteProductLinkRulesSucceeded());
};
