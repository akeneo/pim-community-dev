import React, {StrictMode} from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneomeasure/js/app/shared/theme';
import {Index} from 'akeneomeasure/js/app/pages/index/Index';
import {LegacyContext, LegacyContextValue} from 'akeneomeasure/js/app/shared/legacy/legacy-context';
import {TranslateContext, TranslateContextValue} from 'akeneomeasure/js/app/shared/translate/translate-context';

interface Props {
    dependencies: {
        legacy: LegacyContextValue;
        translate: TranslateContextValue;
    };
}

export default ({dependencies}: Props) => (
    <StrictMode>
        <TranslateContext.Provider value={dependencies.translate}>
            <LegacyContext.Provider value={dependencies.legacy}>
                <ThemeProvider theme={akeneoTheme}>
                    <Router>
                        <Switch>
                            <Route path='/configuration/measurements'>
                                <Index/>
                            </Route>
                        </Switch>
                    </Router>
                </ThemeProvider>
            </LegacyContext.Provider>
        </TranslateContext.Provider>
    </StrictMode>
);
