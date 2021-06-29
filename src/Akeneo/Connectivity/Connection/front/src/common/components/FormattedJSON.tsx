import React, {FC, useMemo} from 'react';
import {createGlobalStyle} from 'styled-components';
import {theme, Theme} from '../styled-with-theme';

const GlobalStyle = createGlobalStyle<Theme>`
  .keyJsonFormatted {
    color: ${({color}) => color.purple120};
  }
  .stringJsonFormatted {
    color: ${({color}) => color.red120};
  }
  .numberJsonFormatted .booleanJsonFormatted {
    color: ${({color}) => color.blue120};
  }
  .nullJsonFormatted {
    color: ${({color}) => color.grey120};
  }
`;

function syntaxHighlight(json: string) {
    json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    const reg = /("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+-]?\d+)?)/g;

    return {
        __html: json.replace(reg, match => {
            let className = 'numberJsonFormatted';
            if (/^"/.test(match)) {
                if (/:$/.test(match)) {
                    className = 'keyJsonFormatted';
                } else {
                    className = 'stringJsonFormatted';
                }
            } else if (/true|false/.test(match)) {
                className = 'booleanJsonFormatted';
            } else if (/null/.test(match)) {
                className = 'nullJsonFormatted';
            }

            return '<span class="' + className + '">' + match + '</span>';
        }),
    };
}

const FormattedJSON: FC = ({children}) => {
    const html = useMemo(() => syntaxHighlight(JSON.stringify(children, undefined, 4)), [children]);

    return (
        <>
            <GlobalStyle {...theme} />
            <pre dangerouslySetInnerHTML={html} />
        </>
    );
};

export default FormattedJSON;
