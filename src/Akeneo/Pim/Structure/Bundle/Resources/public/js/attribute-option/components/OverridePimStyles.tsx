import React from 'react';

const OverridePimStyle = () => {
    const style = {
        __html: `
        .AknDefault-mainContent {
            padding-bottom: 0px;
        }`
    };

    return (
        <style dangerouslySetInnerHTML={style} />
    );
};

export default OverridePimStyle;
