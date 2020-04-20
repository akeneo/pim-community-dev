import styled, {ThemedStyledInterface} from 'styled-components';
import searchIconUrl from './assets/icons/search.svg';

const theme = {
    color: {
        blue10: '#f5f9fc',
        blue100: '#5992c7',
        grey100: '#a1a9b7',
        grey120: '#67768a',
        grey140: '#11324d',
        grey60: '#f9f9fb',
        grey70: '#e8ebee',
        grey80: '#d9dde2',
        purple100: '#9452ba',
        red100: '#d4604f',
        yellow10: '#fef7ec',
    },
    fontSize: {
        title: '30px',
        bigger: '17px',
        big: '15px',
        default: '13px',
        small: '11px',
    },
    icon: {
        search: searchIconUrl,
    },
};

export {theme};
export default styled as ThemedStyledInterface<typeof theme>;
