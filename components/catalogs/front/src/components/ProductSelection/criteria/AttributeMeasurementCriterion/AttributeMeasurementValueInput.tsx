import React, {ChangeEvent, FC, useMemo, useState} from 'react';
import {
    AkeneoThemedProps,
    ArrowDownIcon,
    Dropdown,
    getColor,
    getFontSize,
    GroupsIllustration,
    Search,
} from 'akeneo-design-system';
import {AttributeMeasurementCriterionState} from './types';
import {useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {MeasurementUnit} from '../../../../models/MeasurementUnit';
import {useMeasurements} from '../../../../hooks/useMeasurements';
import {useNumberValue} from '../../hooks/useNumberValue';

const InputsContainer = styled.div`
    display: flex;
    flex-wrap: 'nowrap';
    width: 100%;
`;

const TextInputContainer = styled.div`
    position: relative;
    display: flex;
    flex-direction: column;
    width: 100%;
`;

const ValueInput = styled.input`
    width: 100%;
    height: 40px;
    border: 1px solid ${({invalid}) => (invalid ? getColor('red', 100) : getColor('grey', 80))};
    border-right: 0;
    border-radius: 2px 0 0 2px;
    box-sizing: border-box;
    background: ${getColor('white')};
    color: ${getColor('grey', 140)};
    font-size: ${getFontSize('default')};
    line-height: 40px;
    padding: 0 15px 0 15px;
    outline-style: none;
    cursor: auto;

    &:focus-within {
        box-shadow: 0 0 0 2px ${getColor('blue', 40)};
    }

    &::placeholder {
        opacity: 1;
        color: ${getColor('grey', 100)};
    }
`;

const MeasurementInput = styled.input<{invalid: boolean; isOpen: boolean} & AkeneoThemedProps>`
    width: 100%;
    height: 40px;
    border: 1px solid ${({invalid}) => (invalid ? getColor('red', 100) : getColor('grey', 80))};
    border-left: 0;
    border-radius: 0 2px 2px 0;
    box-sizing: border-box;
    background: ${({isOpen}) => (isOpen ? getColor('grey', 20) : getColor('white'))};
    color: ${getColor('grey', 100)};
    font-size: ${getFontSize('default')};
    line-height: 40px;
    padding: 0 35px 0 15px;
    outline-style: none;
    cursor: pointer;
    overflow: hidden;
    text-overflow: ellipsis;

    &:focus-within {
        box-shadow: 0 0 0 2px ${getColor('blue', 40)};
    }

    &::placeholder {
        opacity: 1;
        color: ${getColor('grey', 100)};
    }
`;

const MeasurementInputArrowDownIcon = styled(ArrowDownIcon)`
    position: absolute;
    right: 0;
    top: 0;
    margin: 12px;
    color: ${getColor('grey', 100)};
`;

type Props = {
    state: AttributeMeasurementCriterionState;
    onChange: (state: AttributeMeasurementCriterionState) => void;
    isInvalid: boolean;
    measurementFamily: string | null;
};

const AttributeMeasurementValueInput: FC<Props> = ({state, onChange, isInvalid, measurementFamily}) => {
    const translate = useTranslate();
    const [isOpen, setIsOpen] = useState<boolean>(false);
    const [search, setSearch] = useState<string>('');
    const {data: measurements} = useMeasurements(measurementFamily);

    const filteredMeasurements: MeasurementUnit[] = useMemo(() => {
        const regex = new RegExp(search, 'i');

        return measurements ? measurements.filter(measurement => measurement.label.match(regex)) : [];
    }, [measurements, search]);

    const findMeasurementLabelByCode = (code?: string | null): string => {
        if (!code || !measurements) {
            return '';
        }

        const result = measurements.find(measurement => measurement.code === code);

        return result ? result.label : '';
    };

    const handleNewMeasurement = (measurement: MeasurementUnit) => {
        onChange({...state, value: {amount: state.value?.amount ?? 0, unit: measurement.code}});
        setIsOpen(false);
    };

    const [value, onValueChange] = useNumberValue(state.value?.amount ?? null, value =>
        onChange({
            ...state,
            value: {
                amount: value,
                unit: state.value?.unit ?? null,
            },
        })
    );

    return (
        <InputsContainer>
            <TextInputContainer>
                <ValueInput
                    onChange={(event: ChangeEvent<HTMLInputElement>) => onValueChange(event.currentTarget.value)}
                    value={value}
                    invalid={isInvalid}
                    data-testid='value'
                />
            </TextInputContainer>
            <Dropdown>
                <TextInputContainer onClick={() => setIsOpen(!isOpen)}>
                    <MeasurementInput
                        type='text'
                        placeholder={translate('akeneo_catalogs.product_selection.criteria.measurement.search')}
                        readOnly={true}
                        title={findMeasurementLabelByCode(state.value?.unit)}
                        value={findMeasurementLabelByCode(state.value?.unit)}
                        data-testid='unit'
                        invalid={isInvalid}
                        isOpen={isOpen}
                    />
                    <MeasurementInputArrowDownIcon size={16} />
                </TextInputContainer>
                {isOpen && (
                    <Dropdown.Overlay
                        onClose={() => setIsOpen(false)}
                        verticalPosition='down'
                        dropdownOpenerVisible={true}
                    >
                        <Dropdown.Header>
                            <Search
                                onSearchChange={setSearch}
                                placeholder={translate('akeneo_catalogs.product_selection.criteria.measurement.search')}
                                searchValue={search}
                                title={translate('akeneo_catalogs.product_selection.criteria.measurement.search')}
                            />
                        </Dropdown.Header>
                        <Dropdown.ItemCollection
                            noResultIllustration={<GroupsIllustration />}
                            noResultTitle={translate(
                                'akeneo_catalogs.product_selection.criteria.measurement.no_results'
                            )}
                        >
                            {filteredMeasurements?.map(measurement => (
                                <Dropdown.Item key={measurement.code} onClick={() => handleNewMeasurement(measurement)}>
                                    {measurement.label}
                                </Dropdown.Item>
                            ))}
                        </Dropdown.ItemCollection>
                    </Dropdown.Overlay>
                )}
            </Dropdown>
        </InputsContainer>
    );
};

export {AttributeMeasurementValueInput};
