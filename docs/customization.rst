Customization
=============


How to create new page type
---------------------------

Backend
"""""""
Go to the `BACKEND_URL/admin/structure/types/add` and create new content type.

Example content type id: `portfolio`

Add field for body blocks (entity reference on paragraphs) or add existing field for it (``field_body_blocks``)

Go to the tab "Manage form display" and configure widgets for fields. Change widget for ``field_body_blocks`` to Paragraphs Browser Experimental and change config for it: **Paragraphs Browser: Content**

After that you need to create new content. Go to `BACKEND_URL/node/add` and create your content.
Add couple of blocks to ``field_body_blocks``.

.. important:: Don't forget to add **URL ALIAS**. Your page will be available on `FRONTEND_URL/URL_ALIAS`. Example url alias: ``/my-portfolio``

Frontend
""""""""
Create new file with name ``YOUR_CONTENT_TYPE_ID.js`` in the ``pages/node`` directory.

Example:

.. code-block:: js
   :linenos:

    // pages/node/portfolio.js

    import React from 'react';
    import PropTypes from 'prop-types';
    import Paragraphs from '../../components/Paragraphs';

    const Portfolio = ({ entity, blocks }) => (
      <div>
        <Paragraphs blocks={blocks} entity={entity} />
      </div>
    );

    Portfolio.defaultProps = {
      entity: '',
      blocks: [],
    };

    Portfolio.propTypes = {
      entity: PropTypes.shape({
        title: PropTypes.array,
      }),
      blocks: PropTypes.arrayOf(PropTypes.shape),
    };

    export default Portfolio;

Open your page. Example: `http://frontend.docker.localhost/my-portfolio`


How to create new block on the page
-----------------------------------

Backend
"""""""

Go to the `BACKEND_URL/admin/structure/paragraphs_type` and create new paragraph type (Type of body block) with needed fields.
New body block will be available on the frontend by paragraph_type_id.

Example: Create new Paragraph type ``Video`` (paragraph type id - ``video``) with field ``Video url`` (field id - ``field_video_url``, field type - text plain)

We should chose witch content type and witch body block field can use new Block.
Sometimes we need to reconfigure field and add new block to it.

Create/edit content and add new body block to it.

Frontend
""""""""
Add transformation for new body block to `utils/transforms.blocks.js`

Example:

.. code-block:: javascript
   :linenos:

   // utils/transforms.blocks.js
   import * as field from '@systemseed/falcon/utils/transforms.fields';

   export default {
      ...
      ...

     /**
      * Handles "Video" body block.
      * Note: key should be the same as paragraph type id.
      */
     video: block => ({
       videoUrl: field.getTextValue(block, 'field_video_url'),
     }),

     ...
     ...
   };


Create new component for new body block in the ''components/BodyBlocks``.

Example:

.. code-block:: javascript
   :linenos:

   // component/BodyBlocks/Video/index.js

   import React from 'react';
   import PropTypes from 'prop-types';

   const Video = ({ videoUrl }) => (
     <div className="bb bb-video">
       <iframe
         title="video"
         width="560"
         height="315"
         src={videoUrl}
         allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
         allowFullScreen
       />
     </div>
   );

   Video.propTypes = {
     videoUrl: PropTypes.string.isRequired,
   };

   export default Video;


More examples you can find  in the ''components/BodyBlocks``.

Add new component to list of body blocks in the ``components/BodyBlocks/index.js``

Additional information
~~~~~~~~~~~~~~~~~~~~~~

If you use your custom field for body blocks you can get list of transformed body blocks.

Example:

.. code-block:: javascript
   :linenos:

   import * as field from '@systemseed/falcon/utils/transforms.fields';
   import transformBodyBlocks from '@systemseed/falcon/utils/transformBodyBlocks';
   import transformsBlocks from '../utils/transforms.blocks'; // list of transforms
   ...
   ...
   // Transform paragraphs on the backend into body blocks on the frontend.
   const blocks = field.getArrayValue(entity, 'field_body_blocks');
   const transformedBlocks = transformBodyBlocks(entity, blocks, transformsBlocks);
   ...

The similar example you can find in ``pages/app.js``

For view body blocks we can use Paragraphs component from ``components/Paragraphs/index.js``

Example:

.. code-block:: javascript
   :linenos:

   import Paragraphs from '../../components/Paragraphs';

   const LandingPage = ({ entityWithBodyBlocks, transformedBlocks }) => (
     <div>
       <Paragraphs blocks={transformedBlocks} entity={entityWithBodyBlocks} />
     </div>
   );


How to add custom settings or modify current (header, footer)
-------------------------------------------------------------

Backend
"""""""

Go to ``BACKEND_URL/admin/structure/config_pages/types`` and manage fields for settings for your site.

You need to add new field.

Example: Add field (text plain) for copyright text in footer (field id - ``field_copyright``)

Go to ``BACKEND_URL/admin/structure/config_pages`` and edit your settings (fill new field).

Additional
~~~~~~~~~~

Sometimes you need to transform data on the backend. For example you have a field (entity reference to content) but on the frontend you want to have only links for content.
For solve the issue you should create new Normalizer for your field/paragraph/entity. See example in the `Rest Entity Recursive <https://www.drupal.org/project/rest_entity_recursive>`_ module.

Frontend
""""""""

If we want to have new field as a props for footer component you need to add transform for field to ``utils/transforms.settings.js`` file.

Example:

.. code-block:: javascript
   :linenos:

   // utils/transforms.settings.js

   import * as field from '@systemseed/falcon/utils/transforms.fields';

   export const footer = (settings) => {
      const props = {};
      ...
      // Footer copyright.
      props.copyright = field.getTextValue(settings, 'field_copyright');
      ...
      return props;
   };

After that you can get ``copyright`` prop in the Footer component.

Also you can find an example how to create new props (like header/footer) in the ``page/_app.js``.


How to add sass to component
----------------------------

You can easily to add custom styles to component. All of you need it is to create new file with styles for your component.

Example:

.. code-block:: css
   :linenos:

   // components/BodyBlocks/Video/_styles.scss

   .embed-video-container {
     position: relative;
     padding-bottom: 56.25%; /* 16:9 */
     height: 0;
     overflow: hidden;
     max-width: 100%;

     & iframe {
       position: absolute;
       top: 0;
       left: 0;
       width: 100%;
       height: 100%;
     }
   }

And add our styles to component.

.. code-block:: javascript
   :linenos:

   // components/BodyBlocks/Video/index.js

   import React from 'react';
   import PropTypes from 'prop-types';

   import './_styles.scss';

   const Video = ({ videoUrl }) => (
     <div className="bb bb-video">
       <iframe
   ...
   ...



How to add custom store/saga
----------------------------

Example:

.. code-block:: javascript
   :linenos:

   // store/store.js

   // Falcon redux createStore function.
   import createStore from '@systemseed/falcon/redux/createStore';
   // Import all our custom reducers.
   import reducers from './reducers';

   function configureStore(initialState) {
     return createStore(reducers, initialState);
   }

   export default configureStore;


You can configure your redux store as you want. We added just one function on top of default redux createStore function for combine user's reducers with falcon reducers.

One more example you can find in ``store/store.js`` file.

.. important:: createStore function should be imported from @systemseed/falcon/redux/createStore.js.

.. important:: We need to pass object of reducers (Not combine Reducers). Function from falcon will be combine all reducers and will be add it own reducers.

Also we recommend to follow falcon structure and to create new reducers in the ``store/reducers`` directory.

Example:

.. code-block:: javascript
   :linenos:

   // store/reducers/cart.js

   export default (state = {}, action) => {
     switch (action.type) {
       case 'CLEAR_CART':
         return {};

       default:
         return state;
     }
   };

And add new reducer to list of reducers.

.. code-block:: javascript
   :linenos:

   // store/reducers/index.js

   import cart from './cart';

   export default {
      ...
      cart,
      ...
   };

New actions should be created in ``store/actions`` directory.

Example:

.. code-block:: javascript
   :linenos:

   // store/actions/cart.js

   /**
    * Clear cart action.
    */
   export const clearCart = () => ({
     type: 'CLEAR_CART',
   });

Also you can find an example how to configure store with redux saga in ``store/store.js`` file.
