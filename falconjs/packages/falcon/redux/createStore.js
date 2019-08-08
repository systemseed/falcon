import { createStore, combineReducers } from 'redux';
import falconReducers from './reducers';

module.exports = (reducers, preloadedState, enhancer) => {
  let withFalconReducers = falconReducers;
  if (reducers) {
    withFalconReducers = { ...reducers, ...falconReducers };
  }

  return createStore(combineReducers(withFalconReducers), preloadedState, enhancer);
};
