const express = require("express");
const app = express()

app.use(express.json()) // for parsing application/json
app.use(express.urlencoded({ extended: true })) // for parsing application/x-www-form-urlencoded


// Home
app.get('/', (req, res) => {
    res.send('Hello World');
});

app.post('/auth/realms/connect/protocol/openid-connect/token', (req, res) => {
    console.log('/connect', req.body, req.body.client_id !== 'portal');
    if (req.body.client_id !== 'portal') {
        res.status(403).json({
            "error": "invalid_client",
            "error_description": "Invalid client credentials"
        });

        return;
    }

    res.json({
        "access_token": "xxxxxxxxxxxxxxx",
        "expires_in": 300,
        "refresh_expires_in": 7776000,
        "refresh_token": "zzzzzzzzzz",
        "token_type": "Bearer",
        "not-before-policy": 1623946804,
        "session_state": "f53508c7-f74b-4b6b-b5c0-bd9fbbd6a127",
        "scope": "email profile"
    })
});


app.post('/api/v1/console/trial/invite', (req, res) => {
    console.log('/invite', req.body);
    const fqdn = req.body.fqdn;
    const email = req.body.email;

    // @todo handle validation of fqdn and email (required, not empty ...)

    res.json({
        OK: true
    })
});

// Start Server listening on the 3000 port
app.listen(3000, () => {
  console.log("Server Ready")
});
