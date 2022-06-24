import {useRoute} from "@akeneo-pim-community/shared/lib/hooks/useRoute";
import {useEffect, useState} from "react";
import {ConnectionCheck} from "../components/StorageConfigurator";
import {LocalStorage, NoneStorage, SftpStorage} from "../components";

const useCheckConnection = (storage: LocalStorage | SftpStorage | NoneStorage) => {
    const route = useRoute('pimee_job_automation_get_storage_connection_check:');
    const [checkData, setCheckData] = useState<ConnectionCheck>();

    useEffect(() => {
    const fetchCheckConnection = async () => {
        const response = await fetch(route, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(storage)
        })

        if (response.ok) {
            const data: ConnectionCheck = await response.json();
            setCheckData(data);
        }
    }

        void fetchCheckConnection();
    }, [storage]);

    return [checkData] as const;
}

export {useCheckConnection};