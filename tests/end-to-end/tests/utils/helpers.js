import { ClientFunction } from 'testcafe';

export const getRequestResult = ClientFunction(url => {
  return new Promise(resolve => {
    const xhr = new XMLHttpRequest();

    xhr.open('GET', url);

    xhr.onload = function () {
      resolve(xhr.status);
    };

    xhr.send(null);
  });
});
