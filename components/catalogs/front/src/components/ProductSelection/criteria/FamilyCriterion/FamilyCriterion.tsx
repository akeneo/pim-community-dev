import React, {FC, useEffect, useState} from 'react';
import {CloseIcon, IconButton, List, MultiSelectInput} from 'akeneo-design-system';
import {Operator} from '../../models/Operator';
import {CriterionModule} from '../../models/Criterion';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/shared';
import {FamilyCriterionState} from './types';
import {useInfiniteFamilies} from '../../hooks/useInfiniteFamilies';
import {FamilyOperatorInput} from './FamilyOperatorInput';
import {FamilySelectInput} from './FamilySelectInput';

const Inputs = styled.div`
    display: flex;
    gap: 20px;
`;

const FamilyCriterion: FC<CriterionModule<FamilyCriterionState>> = ({state, onChange, onRemove}) => {
    const translate = useTranslate();
    const [showFamilies, setShowFamilies] = useState<boolean>(false);
    // const {data, fetchNextPage} = useFamilies();
    // console.log(data);

    useEffect(() => {
        setShowFamilies([Operator.IN_LIST, Operator.NOT_IN_LIST].includes(state.operator));
    }, [state.operator]);

    // const options = data?.pages.map(families => {
    //     return (
    //         <>
    //             {families.data.map(({code, label}) => {
    //                 return (
    //                     <MultiSelectInput.Option title={label} value={code} key={code}>
    //                         {label}
    //                     </MultiSelectInput.Option>
    //                 );
    //             })}
    //         </>
    //     );
    // });

    return (
        <List.Row>
            <List.TitleCell width={150}>
                {translate('akeneo_catalogs.product_selection.criteria.status.label')}
            </List.TitleCell>
            <List.Cell width='auto'>
                <Inputs>
                    <FamilyOperatorInput state={state} onChange={onChange} />
                    {showFamilies && <FamilySelectInput state={state} onChange={onChange} />}
                </Inputs>
            </List.Cell>
            <List.RemoveCell>
                <IconButton ghost='borderless' level='tertiary' icon={<CloseIcon />} title='' onClick={onRemove} />
            </List.RemoveCell>
        </List.Row>
    );
};

export {FamilyCriterion};

// {showFamilies && (
//     <MultiSelectInput
//         onChange={v => onChange({...state, value: v})}
//         emptyResultLabel={translate('akeneo_catalogs.product_selection.criteria.family.no_matches')}
//         openLabel=''
//         onNextPage={fetchNextPage}
//         placeholder=''
//         value={state.value}
//         removeLabel=''
//         data-testid='value'
//     >
//         {/*{options}*/}
//         <MultiSelectInput.Option title='Option 0' value='Option 0'>
//             Option 0
//         </MultiSelectInput.Option>
//     </MultiSelectInput>
// )}
