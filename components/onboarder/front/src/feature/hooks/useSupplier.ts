import {useCallback, useEffect, useMemo, useState} from 'react';
import {NotificationLevel, useNotify, useRoute, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {Supplier} from '../models';

const useSupplier = (identifier: string) => {
    const getSupplierRoute = useRoute('onboarder_serenity_supplier_edit', {identifier});
    const saveSupplierRoute = useRoute('onboarder_serenity_supplier_edit', {identifier});
    const [originalSupplier, setOriginalSupplier] = useState<Supplier | null>(null);
    const [supplier, setSupplier] = useState<Supplier | null>(null);
    const [validationErrors, setValidationErrors] = useState<ValidationError[]>([]);
    const notify = useNotify();
    const translate = useTranslate();

    const loadSupplier = useCallback(async () => {
        const response = await fetch(getSupplierRoute, {method: 'GET'});

        if (!response.ok) {
            notify(NotificationLevel.ERROR, translate('onboarder.supplier.supplier_edit.error'));
            return;
        }

        const responseBody = await response.json();
        setOriginalSupplier(responseBody);
        setSupplier(responseBody);
    }, [getSupplierRoute, notify, translate, setOriginalSupplier, setSupplier]);

    const saveSupplier = useCallback(async () => {
        const response = await fetch(saveSupplierRoute, {
            method: 'PUT',
            headers: [
                ['Content-type', 'application/json'],
                ['X-Requested-With', 'XMLHttpRequest'],
            ],
            body: JSON.stringify(supplier),
        });

        if (!response.ok) {
            setValidationErrors(await response.json());
            notify(NotificationLevel.ERROR, translate('onboarder.supplier.supplier_edit.unknown_error'));
            return;
        }

        notify(NotificationLevel.SUCCESS, translate('onboarder.supplier.supplier_edit.success_message'));
        await loadSupplier();
    }, [saveSupplierRoute, supplier, notify, translate, loadSupplier]);

    const supplierHasChanges = useMemo(() => {
        if (null !== supplier) {
            return JSON.stringify(supplier) !== JSON.stringify(originalSupplier);
        }

        return false;
    }, [supplier, originalSupplier]);

    useEffect(() => {
        loadSupplier();
    }, [loadSupplier]);

    return [supplier, setSupplier, supplierHasChanges, saveSupplier, validationErrors] as const;
};

export {useSupplier};
