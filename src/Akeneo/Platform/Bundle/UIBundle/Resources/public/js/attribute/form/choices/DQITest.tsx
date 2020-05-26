import React, {useEffect} from 'react';

const mediator = require('oro/mediator');

const DQITest = () => {

    useEffect(() => {
        const handleOptions = (options: any) => {
            mediator.trigger('pim:attribute-options:enrichRender', {
                black: [<div key="test1">test</div>, <div key="test2">test 2</div>],
            });
        };

        mediator.on('pim:attribute-options:list', handleOptions);

        return () => {
            mediator.off('pim:attribute-options:list', handleOptions);
        }
    }, []);

    return (<></>);
}

export default DQITest;
