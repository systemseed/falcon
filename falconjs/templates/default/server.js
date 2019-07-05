/* eslint-disable no-console */
const express = require('express');
const nextjs = require('next');
const dotenv = require('dotenv');
const applyFalconRoutesConfiguration = require('@systemseed/falcon/routes/server.js');
const { globalSettingsForApp } = require('@systemseed/falcon/routes/globalSettings');
const decoupledRouter = require('@systemseed/falcon/routes/decoupledRouter');
const { customRoutes } = require('@systemseed/falcon/routes/customRoutes');

// Import variables from local .env file.
dotenv.config();

const port = process.env.PORT || 3000;
const dev = process.env.NODE_ENV !== 'production';
const app = nextjs({ dev });

app
  .prepare()
  .then(() => {
    // Initialize express.js server.
    const expressServer = express();

    applyFalconRoutesConfiguration(app, expressServer);

    expressServer.use(globalSettingsForApp(app, process.env.SETTINGS_NAME));
    //expressServer.use(globalSettings(app, 'settings2'));

    // List of routes which exist only in the frontend application.
    const routes = [
      {
        route: '/custom-route',
        path: '/cookie-table',
      },
    ];
    expressServer.use(customRoutes(app, routes));

    // Handle all other requests using our custom router which is a mix
    // or original Next.js logic and Drupal routing logic.
    expressServer.get('*', (req, res) => decoupledRouter(req, res, app));

    expressServer.listen(port, (err) => {
      if (err) throw err;
      console.log(`> Application is ready on ${process.env.FRONTEND_URL}`);
    });
  })
  .catch(err => console.error(err));
