// Falcon redux createStore function.
import createStore from '@systemseed/falcon/redux/createStore';
import { applyMiddleware } from 'redux';

// Logger/debugger for Redux store: https://github.com/zalmoxisus/redux-devtools-extension#14-using-in-production
import { composeWithDevTools } from 'redux-devtools-extension/logOnlyInProduction';

// Sagas!
import createSagaMiddleware from 'redux-saga';

// Import listener which can convert redux actions into promises;
import createReduxPromiseListener from 'redux-promise-listener';

// Import all our custom sagas.
import sagas from './sagas';

// Import all our custom reducers.
import reducers from './reducers';

// Create a saga middleware.
const sagaMiddleware = createSagaMiddleware();

// Create a middleware for conversion of store actions into promises.
const reduxPromiseListener = createReduxPromiseListener();

function configureStore(initialState) {
  // Build store.
  const store = createStore(
    reducers,
    initialState,
    composeWithDevTools(applyMiddleware(sagaMiddleware, reduxPromiseListener.middleware)),
  );

  store.runSagaTask = () => {
    store.sagaTask = sagaMiddleware.run(sagas);
  };

  store.runSagaTask();
  return store;
}

export const promiseListener = reduxPromiseListener;

export default configureStore;
