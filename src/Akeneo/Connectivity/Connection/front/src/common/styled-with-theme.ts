import styled, {ThemedStyledInterface} from 'styled-components';
import searchIconUrl from './assets/icons/search.svg';
import graphIllustrationUrl from './assets/illustrations/Graph.svg';
import apiIllustrationUrl from './assets/illustrations/NewAPI.svg';
import surveyIllustrationUrl from './assets/illustrations/UserSurvey.svg';
import {pimTheme} from 'akeneo-design-system';

const theme = {
    ...pimTheme,
    color: {
        ...pimTheme.color,
        blue10: '#f5f9fc',
        blue100: '#5992c7',
        grey100: '#a1a9b7',
        grey120: '#67768a',
        grey140: '#11324d',
        grey20: '#f6f7fb',
        grey60: '#e8ebee',
        grey80: '#c7cbd4',
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
        metricsBig: '22px',
    },
    icon: {
        search: searchIconUrl,
    },
    illustration: {
        api: apiIllustrationUrl,
        graph: graphIllustrationUrl,
        survey: surveyIllustrationUrl,
    },
};

type Theme = typeof theme;

export {theme, Theme};
export default styled as ThemedStyledInterface<Theme>;
