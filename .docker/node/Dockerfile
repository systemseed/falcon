ARG NODE_TAG
FROM wodby/node:$NODE_TAG

COPY --chown=node:node ./tests/end-to-end /tests
WORKDIR /falconjs

RUN yarn build

CMD [ "yarn", "start:dev" ]
