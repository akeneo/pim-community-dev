import React, {FC} from 'react';
import {mergeRoutes} from '@src/infrastructure/routing';

const ComponentA: FC = () => (<div>A</div>);
const ComponentB: FC = () => (<div>B</div>);
const ComponentC: FC = () => (<div>C</div>);
const ComponentD: FC = () => (<div>D</div>);

test('it merges routes', () => {
    const original = [
        {
            path: '/foo',
            component: ComponentA,
        },
        {
            path: '/bar',
            component: ComponentB,
        },
    ];

    const overrides = [
        {
            path: '/bar',
            component: ComponentC,
        },
        {
            path: '/foobar',
            component: ComponentD,
        },
    ];

    const result = mergeRoutes(original, overrides);

    expect(result).toEqual([
        {
            path: '/foo',
            component: ComponentA,
        },
        {
            path: '/bar',
            component: ComponentC,
        },
        {
            path: '/foobar',
            component: ComponentD,
        },
    ]);
});
