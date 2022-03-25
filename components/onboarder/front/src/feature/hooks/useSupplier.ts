import {useCallback, useEffect, useState} from 'react';
import {useNotify, useRoute, useTranslate, NotificationLevel} from '@akeneo-pim-community/shared';
import {Supplier} from '../models';

const useSupplier = (identifier: string) => {
    const [supplier, setSupplier] = useState<Supplier | null>(null);
    const getSupplierRoute = useRoute('onboarder_serenity_supplier_edit', {identifier});
    const saveSupplierRoute = useRoute('onboarder_serenity_supplier_edit', {identifier});
    const notify = useNotify();
    const translate = useTranslate();
    const [updatedLabel, setLabel] = useState('');

    const saveSupplier = useCallback(async () => {
        await fetch(saveSupplierRoute, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                label: updatedLabel,
            }),
        });

        notify(NotificationLevel.SUCCESS, translate(''));
    }, [saveSupplierRoute, updatedLabel]); // eslint-disable-line react-hooks/exhaustive-deps

    useEffect(() => {
        (async () => {
            const response = await fetch(getSupplierRoute, {method: 'GET'});

            if (!response.ok) {
                notify(NotificationLevel.ERROR, translate('onboarder.supplier.supplier_edit.error'));
                return;
            }

            setSupplier(await response.json());
        })();
    }, [getSupplierRoute]); // eslint-disable-line react-hooks/exhaustive-deps

    return {supplier, setLabel, saveSupplier};
};

export {useSupplier};
