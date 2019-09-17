ARG NODE_TAG
FROM wodby/node:$NODE_TAG

COPY --chown=node:node ./falconjs /falconjs

WORKDIR /falconjs

RUN yarn build

CMD [ "yarn", "start:dev" ]
