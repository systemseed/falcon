/* eslint-disable no-console */
const { startFalconServer } = '@systemseed/falcon/routing/server';
const dotenv = require('dotenv');
const favicon = require('serve-favicon');
const applyFalconRoutingConfiguration = require('@systemseed/falcon/routing/server.js');
const { globalSettingsForApp, handleHomepageRequest } = require('@systemseed/falcon/routing/globalSettings');
const decoupledRouter = require('@systemseed/falcon/routing/decoupledRouter');
const clearCache = require('@systemseed/falcon/routing/clearCache');
const frontendOnlyRoutes = require('@systemseed/falcon/routing/frontendOnlyRoutes');
const routes = require('./routes');
const falconConfig = require('./config');

// Import variables from the local .env file.
dotenv.config();

// Define if we want to run server in dev or production mode.
const dev = process.env.NODE_ENV !== 'production';

startFalconServer({ dev })
  .then(expressServer => {

    // Run the prepared express.js server on the desired port.
    const port = process.env.PORT || 3000;
    expressServer.listen(port, error => {
      if (error) throw error;
      console.log(`> Application is ready on ${falconConfig.FRONTEND_URL}`);
    });

  })
  .catch(error => {
      console.log(error);
  });
