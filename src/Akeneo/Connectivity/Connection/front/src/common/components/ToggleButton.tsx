import React, {DetailedHTMLProps, forwardRef, InputHTMLAttributes, Ref, useState} from 'react';
import {useTranslate} from '../../shared/translate';

type InputProps = DetailedHTMLProps<InputHTMLAttributes<HTMLInputElement>, HTMLInputElement>;

export const ToggleButton = forwardRef((props: InputProps, ref: Ref<HTMLInputElement>) => {
    const translate = useTranslate();

    const [checked, setChecked] = useState(props.defaultChecked);

    return (
        <div className='switch switch-small has-switch' data-on-label={translate('pim_common.yes')} data-off-label='No'>
            <div className={`switch-animate switch-${checked ? 'on' : 'off'}`}>
                <input
                    type='checkbox'
                    ref={ref}
                    {...props}
                    id={props.id || props.name}
                    onChange={event => setChecked(event.target.checked)}
                />
                <span className='switch-left switch-small'>{translate('pim_common.yes')}</span>
                <label className='switch-small' htmlFor={props.id || props.name}>
                    &nbsp;
                </label>
                <span className='switch-right switch-small'>{translate('pim_common.no')}</span>
            </div>
        </div>
    );
});
