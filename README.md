# Installation

In modules folder of a Thunder installation:

    git clone git@github.com:thunder/thunder_schema.git
    drush en thunder_schema

Might also want to enable the thunder_demo module to have some articles to work with, if you do not have any.

+ open admin/config/graphql in browser click "Create Server"
+ choose a label and custom endpoint to your liking.
+ Select "Composable schema" as schema and enable all extensions in "Schema configuration"
+ Hit "Save" button

Back on admin/config/graphql choose "Explorer" from the drop down button

If all works, you should be able to test some queries in the Explorer.

# Example

    {
      article(id: 4) {
        id
        name
        language
        author {
          id
          name
          mail
        }
        type
        entity
        channel {
          published
          name
        }
        content {
          id
          ... on ImageListParagraph {
            name
            images {
              name
              src
            }
          }
          ... on EmbedParagraph {
            url
          }
          ... on TextParagraph {
            text
          }
          ... on ImageParagraph {
            src
            width
            title
            alt
            name
            tags {
              name
            }
          }
        }
      }
    }

