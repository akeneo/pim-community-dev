import {useEffect, useState} from 'react';
import {NotificationLevel, useNotify, useRoute, useTranslate} from '@akeneo-pim-community/shared';
import {ContributorEmail, Supplier} from '../models';

const useSupplier = (identifier: string) => {
    const [supplier, setSupplier] = useState<Supplier | null>(null);
    const getSupplierRoute = useRoute('onboarder_serenity_supplier_edit', {identifier});
    const saveSupplierRoute = useRoute('onboarder_serenity_supplier_edit', {identifier});
    const [supplierLabel, setSupplierLabel] = useState('');
    const [supplierContributors, setSupplierContributors] = useState<ContributorEmail[]>([]);
    const notify = useNotify();
    const translate = useTranslate();

    useEffect(() => {
        if (null !== supplier) {
            setSupplierLabel(supplier.label);
            setSupplierContributors(supplier.contributors);
        }
    }, [supplier]);

    const saveSupplier = async() => {
        const response = await fetch(saveSupplierRoute, {
            method: 'PUT',
            headers: [
                ['Content-type', 'application/json'],
                ['X-Requested-With', 'XMLHttpRequest'],
            ],
            body: JSON.stringify({label: supplierLabel, contributorEmails: supplierContributors}),
        });

        if (!response.ok) {
            notify(NotificationLevel.ERROR, translate('onboarder.supplier.supplier_edit.unknown_error'));
            return;
        }

        loadSupplier();
        notify(NotificationLevel.SUCCESS, translate('onboarder.supplier.supplier_edit.sucess_message'));
    };

    const loadSupplier = async() => {
        const response = await fetch(getSupplierRoute, {method: 'GET'});

        if (!response.ok) {
            notify(NotificationLevel.ERROR, translate('onboarder.supplier.supplier_edit.error'));
            return;
        }

        setSupplier(await response.json());
    }

    const supplierHasChanges = () => {
        if (null !== supplier) {
            return supplierLabel !== supplier.label || JSON.stringify(supplierContributors) !== JSON.stringify(supplier.contributors);
        }

        return false;
    };

    const isSupplierLoaded = () => supplier !== null;

    useEffect(() => {
        (async () => {
            await loadSupplier();
        })();
    }, [getSupplierRoute]); // eslint-disable-line react-hooks/exhaustive-deps

    return {
        supplierCode: null !== supplier ? supplier.code : '',
        supplierLabel,
        setSupplierLabel,
        supplierContributors,
        setSupplierContributors,
        supplierHasChanges,
        saveSupplier,
        isSupplierLoaded
    };
};

export {useSupplier};
