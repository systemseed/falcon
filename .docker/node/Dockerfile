ARG NODE_TAG
FROM wodby/node:$NODE_TAG

COPY --chown=node:node ./falconjs /app

WORKDIR /app

RUN yarn build

CMD [ "yarn", "start" ]
