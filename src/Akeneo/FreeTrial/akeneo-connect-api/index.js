const express = require('express');
const app = express();

app.use(express.json()); // for parsing application/json
app.use(express.urlencoded({extended: true})); // for parsing application/x-www-form-urlencoded

// Home
app.get('/', (req, res) => {
  res.send('Hello World');
});

app.post('/auth/realms/connect/protocol/openid-connect/token', (req, res) => {
  console.log('/connect', req.body, req.body.client_id !== 'portal');
  if (req.body.client_id !== 'portal') {
    res.status(403).json({
      error: 'invalid_client',
      error_description: 'Invalid client credentials',
    });

    return;
  }

  res.json({
    access_token: 'xxxxxxxxxxxxxxx',
    expires_in: 300,
    refresh_expires_in: 7776000,
    refresh_token: 'zzzzzzzzzz',
    token_type: 'Bearer',
    'not-before-policy': 1623946804,
    session_state: 'f53508c7-f74b-4b6b-b5c0-bd9fbbd6a127',
    scope: 'email profile',
  });
});

const invitedUsers = [];

app.post('/api/v1/console/trial/invite', (req, res) => {
  console.log('/invite', req.body);
  const fqdn = req.body.fqdn;
  const email = req.body.email;

  // @todo we should have localhost instead
  if (fqdn !== 'http://localhost:8080') {
    res.status(400);
    res.json({
      error: 400,
      message: `PIM Trial instance with instance FQDN ${fqdn} not found`,
      type: 'trial.unknown_instance',
    });

    return;
  }

  if (email === 'admin@example.com') {
    res.status(400);
    res.json({
      error: 400,
      message: `User with email ${email} already has a PIM Trial instance`,
      type: 'trial.already_registered_user',
    });

    return;
  }

  if (
    ![
      'Sandra@example.com',
      'Peter@example.com',
      'Mary@example.com',
      'Julien@example.com',
      'Julia@example.com',
    ].includes(email)
  ) {
    res.status(400);
    res.json({
      error: 400,
      message: 'The request is invalid, please see the project console API documentation for more information',
      type: 'trial.invalid_parameters', // @todo How are validate the emails?
    });

    return;
  }

  if (invitedUsers.includes(email)) {
    res.status(400);
    res.json({
      error: 400,
      message: `User with email ${email} already has a PIM Trial instance invitation`,
      type: 'trial.already_invited_user',
    });

    return;
  }

  invitedUsers.push(email);

  res.json({
    OK: true,
  });
});

// Start Server listening on the 3000 port
app.listen(3000, () => {
  console.log('Server Ready');
});
