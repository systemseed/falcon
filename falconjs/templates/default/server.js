/* eslint-disable no-console */
const { falconApp } = '@systemseed/falcon/routing/server';
const dotenv = require('dotenv');
const favicon = require('serve-favicon');
const applyFalconRoutingConfiguration = require('@systemseed/falcon/routing/server.js');
const { globalSettingsForApp, handleHomepageRequest } = require('@systemseed/falcon/routing/globalSettings');
const decoupledRouter = require('@systemseed/falcon/routing/decoupledRouter');
const clearCache = require('@systemseed/falcon/routing/clearCache');
const frontendOnlyRoutes = require('@systemseed/falcon/routing/frontendOnlyRoutes');
const routes = require('./routes');
const falconConfig = require('./config');

// Import variables from local .env file.
dotenv.config();

const port = process.env.PORT || 3000;
const dev = process.env.NODE_ENV !== 'production';

falconApp
  .then(() => {
    // const expressServer = applyFalconRoutingConfiguration(falconApp);

    expressServer.listen(port, (err) => {
      if (err) throw err;
      console.log(`> Application is ready on ${process.env.FRONTEND_URL}`);
    });
  })
  .catch(err => console.error(err));
