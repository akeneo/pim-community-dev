import React, {FC} from 'react';
import ReactDOM from 'react-dom';
import {BrowserRouter as Router, Route, Switch} from 'react-router-dom';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ForgotPasswordConfirmationPage, ForgotPasswordPage, LoginPage, ResetPasswordPage} from './login-rework';

type Props = {
  csrfToken: string;
};
const Wrapper: FC<Props> = ({csrfToken}) => {
  return (
    <Router basename={'/user'}>
      <Switch>
        <Route path={'/login'}>
          <LoginPage csrfToken={csrfToken} />
        </Route>
        <Route path={'/reset-request'}>
          <ForgotPasswordPage />
        </Route>
        <Route path={'/send-mail'}>
          <ForgotPasswordConfirmationPage />
        </Route>
        <Route path={'/reset/:token'}>
          <ResetPasswordPage />
        </Route>
      </Switch>
    </Router>
  );
};

class PimLoginApp {
  readonly el: HTMLElement;

  constructor(element: HTMLElement) {
    this.el = element;
  }

  public render(): PimLoginApp {
    this.hideExistingForm();
    ReactDOM.render(
      React.createElement(
        ThemeProvider,
        {theme: pimTheme},
        React.createElement(
          DependenciesProvider,
          null,
          React.createElement(Wrapper, {
            csrfToken: this.el.dataset.csrfToken || '',
          })
        )
      ),
      this.el
    );
    return this;
  }

  private hideExistingForm(): void {
    document.body.classList.remove('AknLogin');

    const content = document.getElementById('top-page');
    if (content) {
      content.parentNode?.removeChild(content);
    }
  }
}

export = PimLoginApp;
