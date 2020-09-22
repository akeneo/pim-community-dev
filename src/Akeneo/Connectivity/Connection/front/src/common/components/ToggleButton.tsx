import React, {DetailedHTMLProps, forwardRef, InputHTMLAttributes, Ref} from 'react';
import {useTranslate} from '../../shared/translate';

type Props = {
    isChecked: boolean;
}
type InputProps = DetailedHTMLProps<InputHTMLAttributes<HTMLInputElement>, HTMLInputElement>;

export const ToggleButton = forwardRef(({isChecked, ...props}: Props & InputProps, ref: Ref<HTMLInputElement>) => {
    const translate = useTranslate();

    return (
        <div className='switch switch-small has-switch' data-on-label='Yes' data-off-label='No'>
            <div className={`switch-animate switch-${isChecked ? 'on' : 'off'}`}>
                <input id='auto-sort-options' type='checkbox' ref={ref} {...props} />
                <span className='switch-left switch-small'>{translate('Yes')}</span>
                <label className='switch-small' onClick={() => console.log('toggle')} role='toggle-sort-attribute-option' htmlFor='auto-sort-options'>
                    &nbsp;
                </label>
                <span className='switch-right switch-small'>{translate('No')}</span>
            </div>
        </div>
    );
});
