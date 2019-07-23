import { combineReducers } from 'redux';
import falconReducers from '@systemseed/falcon/redux/reducers';

export default combineReducers({
  ...falconReducers,
});
