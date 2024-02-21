import React from 'react';

const OverridePimStyle = () => {
  const style = `
        .AknDefault-mainContent {
            padding-bottom: 0px;
        `;

  return <style>{style}</style>;
};

export default OverridePimStyle;
