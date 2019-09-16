ARG NODE_TAG
FROM wodby/node:$NODE_TAG

COPY --chown=wodby:wodby ./tests/end-to-end /tests

RUN yarn build
CMD [ "yarn", "start" ]
