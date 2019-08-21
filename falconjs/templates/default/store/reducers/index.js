/**
 * @file Provides object with all reducers that we need in Redux.
 * Note: we don't need to use 'combineReducers' because
 * it will be done in the 'createStore' function
 * from @systemseed/falcon/redux/createStore.
 */

import exampleReducer from './example';

export default {
  exampleReducer,
};
