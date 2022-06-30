import {useState} from "react";
import {ConnectionCheck} from "../components/StorageConfigurator";
import {SftpStorage} from "../components";
import {useRoute} from "@akeneo-pim-community/shared/lib/hooks/useRoute";

const useCheckStorageConnection = () => {
    const [check, setCheck] = useState<ConnectionCheck>();
    const [isChecking, setIsChecking] = useState<boolean>(false);
    const route = useRoute('pimee_job_automation_get_storage_connection_check');

    const checkReliability = async (storage: SftpStorage) => {
        setIsChecking(true);
        const response = await fetch(route, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(storage),
        });

        if (response.ok) {
            const data: ConnectionCheck = await response.json();
            setCheck(data);
        }
        setIsChecking(false);
    };

    return [check, setCheck, isChecking, checkReliability] as const;
}

export {useCheckStorageConnection}