/* eslint-disable no-console */
const { startFalconServer } = require('@systemseed/falcon/routing/server.js');
const falconConfig = require('./config');

// Define if we want to run server in dev or production mode.
const dev = process.env.NODE_ENV !== 'production';

startFalconServer(falconConfig, { dev })
  .then((expressServer) => {
    // Run the prepared express.js server on the desired port.
    const port = process.env.PORT || 3000;
    expressServer.listen(port, (error) => {
      if (error) throw error;
      console.log(`> Application is ready on ${falconConfig.FRONTEND_URL}`);
    });
  })
  .catch((error) => {
    console.log(error);
  });
