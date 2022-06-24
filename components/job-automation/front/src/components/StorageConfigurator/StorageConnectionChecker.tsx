import React, {useState} from 'react';
import {ConnectionCheck, StorageConnectionCheckerProps} from "./model";
import {Button, CheckIcon} from "akeneo-design-system";
import styled from "styled-components";
import {useRoute} from "@akeneo-pim-community/shared/lib/hooks/useRoute";
import {LocalStorage, NoneStorage, SftpStorage} from "../model";
import {Helper} from "akeneo-design-system/lib/components/Helper/Helper";
import {pimTheme} from "akeneo-design-system/lib/theme/pim";
import {useTranslate} from "@akeneo-pim-community/shared/lib/hooks/useTranslate";

const Wrapper = styled.div`
  display: flex;
  align-items: center;
  gap: 8.5px;
`

const StorageConnectionChecker = ({storage}: StorageConnectionCheckerProps) => {
    const route = useRoute('pimee_job_automation_get_storage_connection_check');
    const translate = useTranslate();
    const [check, setCheck] = useState<ConnectionCheck>();

    const checkData = async (storage: LocalStorage | SftpStorage | NoneStorage) => {
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
            setCheck(data);
        }
    }

    return <>
        <Wrapper>
            <Button
                onClick={() => {checkData(storage)}}
                disabled={(check && check.is_connection_healthy)}
                level="primary">
                {translate('akeneo.automation.storage.connection_checker.label')}
            </Button>
            {(check && check.is_connection_healthy) ? <CheckIcon color={pimTheme.color.green100}/> : ''}
        </Wrapper>
        <>{ (check && !check.is_connection_healthy) ? <Helper inline level="error">{check.error_message}</Helper> : '' }</>
    </>
}

export {StorageConnectionChecker};