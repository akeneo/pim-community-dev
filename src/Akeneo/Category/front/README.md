# Category Frontend

## run microfrontend standalone
To run the Category UI outside the pim you'll need the PIM running.
All request to any endpoint other than / will be proxied server-side by the node process to the PIM at port 8080.

You need to be authenticated on the PIM for the UI to work properly.

TODO : see if we can catch eventual 401 to
- either display a message to the user to login on PIM
- either (if possible) redirect to localPIM and be redirected back to the microfrontend UI after authentication


