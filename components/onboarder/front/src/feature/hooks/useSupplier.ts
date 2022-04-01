import {useEffect, useState} from 'react';
import {NotificationLevel, useNotify, useRoute, useTranslate} from '@akeneo-pim-community/shared';
import {ContributorEmail, Supplier} from '../models';

const useSupplier = (identifier: string) => {
    const getSupplierRoute = useRoute('onboarder_serenity_supplier_edit', {identifier});
    const saveSupplierRoute = useRoute('onboarder_serenity_supplier_edit', {identifier});
    const [originalSupplier, setOriginalSupplier] = useState<Supplier | null>(null);
    const [supplier, setSupplier] = useState<Supplier | null>(null);
    const notify = useNotify();
    const translate = useTranslate();

    const setSupplierLabel = (newLabel: string) => {
        setSupplier(supplier !== null ? {...supplier, label: newLabel} : null);
    };

    const setSupplierContributors = (newContributors: ContributorEmail[]) => {
        setSupplier(supplier !== null ? {...supplier, contributors: newContributors} : null);
    };

    const saveSupplier = async () => {
        const response = await fetch(saveSupplierRoute, {
            method: 'PUT',
            headers: [
                ['Content-type', 'application/json'],
                ['X-Requested-With', 'XMLHttpRequest'],
            ],
            body: JSON.stringify(supplier),
        });

        if (!response.ok) {
            notify(NotificationLevel.ERROR, translate('onboarder.supplier.supplier_edit.unknown_error'));
            return;
        }

        loadSupplier();
        notify(NotificationLevel.SUCCESS, translate('onboarder.supplier.supplier_edit.sucess_message'));
    };

    const loadSupplier = async () => {
        const response = await fetch(getSupplierRoute, {method: 'GET'});

        if (!response.ok) {
            notify(NotificationLevel.ERROR, translate('onboarder.supplier.supplier_edit.error'));
            return;
        }

        const responseBody = await response.json();
        setOriginalSupplier(responseBody);
        setSupplier(responseBody);
    };

    const supplierHasChanges = () => {
        if (null !== supplier) {
            return JSON.stringify(supplier) !== JSON.stringify(originalSupplier);
        }

        return false;
    };

    useEffect(() => {
        (async () => {
            await loadSupplier();
        })();
    }, [getSupplierRoute]); // eslint-disable-line react-hooks/exhaustive-deps

    return {
        supplier,
        setSupplierLabel,
        setSupplierContributors,
        supplierHasChanges,
        saveSupplier,
    };
};

export {useSupplier};
