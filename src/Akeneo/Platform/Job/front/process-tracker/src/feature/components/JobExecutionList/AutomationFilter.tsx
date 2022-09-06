import React from 'react';
import {Translate, useTranslate} from '@akeneo-pim-community/shared';
import {Checkbox, Dropdown, SwitcherButton, useBooleanState} from 'akeneo-design-system';
import {JobStatus} from "../../models";

type AutomationFilterProps = {
    automationFilterValue: string;
    onAutomationFilterChange: (automationFilterValue: string) => void;
};

const automationFilterValues: Array<string> = ['all', 'yes', 'no'];

const AutomationFilter = ({automationFilterValue, onAutomationFilterChange}: AutomationFilterProps) => {
    const translate = useTranslate();
    const [isDropdownOpen, openDropdown, closeDropdown] = useBooleanState();

    const handleStatusToggle = (status: string) => () => {
        onAutomationFilterChange(automationFilterValue);
    };

    return (
        <Dropdown>
            <SwitcherButton label={translate('akeneo_job_process_tracker.automation_filter.label')} onClick={openDropdown}>
                {automationFilterValue}
            </SwitcherButton>
            {isDropdownOpen && (
                <Dropdown.Overlay onClose={closeDropdown}>
                    <Dropdown.Header>
                        <Dropdown.Title>{translate('akeneo_job_process_tracker.status_filter.label')}</Dropdown.Title>
                    </Dropdown.Header>
                    <Dropdown.ItemCollection>
                        {automationFilterValues.map((value) =>
                            <Dropdown.Item key={value}>
                                <Checkbox
                                    checked={value === automationFilterValue}
                                    onChange={() => onAutomationFilterChange(value)}
                                />
                                {translate(`akeneo_job_process_tracker.automation_filter.${value}`)}
                            </Dropdown.Item>
                        )}
                    </Dropdown.ItemCollection>
                </Dropdown.Overlay>
            )}
        </Dropdown>
    );
}

export {AutomationFilter}
