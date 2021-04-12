# Introduction

The thunder_gqls module provides a GraphQL API schema and implementation for Thunder based on the Drupal GraphQL module
version 4.
Version 4 of the GraphQL module does not provide an out of the box API for Drupal, as preious versions did. Instead it
provides us tha ability to define a schema independent of the underlying Drupal installation, and means to map fields
defined in that schema to data from Drupal.

## Motivation

Drupal core provides already a turn-key implementation for JSON-API, which basically just needs to be enabled and
configured, and it is good to go. Similarly, version 3 of the GraphQl module is as quickly usable. Both modules expose
all data structures from Drupal as they are.

So why did we manually implement an API? While it is very convenient to have schemas automatically created, it also
leads to an API that is very close to the inner workings of Drupal. A consumer would have to know the relationships of
entities within Drupal. Especially when working with paragraphs, and media entities, you would have to be aware of the
Entity references to get to the actual data.
For example, we have Media entities for images in Paragraphs. The referencing goes unconventionally deep in this case.
If you wanted to get the src attribute of an image in such a paragraph, you would have to dereference
Article => Paragraph => Media Entity => File Entity (src).

Another pain point is, that field names are automatically created. This leads to two separate problems. Field names are
awkward and again very Drupal specific. in GraphQl 3 we have entityUuid instead of uuid and fieldMyField instead of
just myField.
Furthermore, since field names are automatically generated of machine name, the API would change, as soon as you change
machine name. This sounds not very likely, and for actual fields it should not really happen, but sometime even plugin
names are used to create the schema, and plugins could be exchanged (we had an example of a views-plugin, that was exchanged).

Finally, routing with those automated APIs is very often a process that requires two requests, instead of one.
Usually you just have some url string, that could be a rout to a node, a user, a term or any other entity. To get #
the actual data, you will have to first do a route query, to get the information what kind of entity we are looking at
(plus its ID), and then we would have to do a specific node, term or user query to get the actual page.

# Basic Ideas

We introduce three main interfaces for which covers all main data types used in Thunder.

1) Page
2) Media
3) Paragraph

The page interface is for all drupal entities that have a URL, in Thunder that could be nodes, terms, users. This gives
us the possibility to request a page from a route without knowing if it is an article or a channel for example.

The Media Interface is for all media entities, and the Paragraph interface for all paragraph entities.

As described above,we try to minimize references and keep fields as flat as possible. Especially if the references are
very drupal specific. Also, drupal specific field prefixes should be avoided, they make no sense for the frontend.

One example would be the Image type, which is implementing the media interface.
In Drupal media entities fields are distributed between several entities, because the file entity does provide
the basic file information and the media entiry adds more data fields to that, while referencing a file. Directly
translated to a GraphQl API it would look similar to:

    type MediaImage {
      entityLabel
      fieldDescription
      fieldImage {
        src
        alt
        width
        height
      }
    }

When you think about images as a frontend developer, you might expect datastructures similar to the following:

    type MediaImage {
      name
      description
      src
      alt
      width
      height
    }

Much cleaner and less noise.

# Usage

The starting point for most requests will be some kind of route

## Routing
## Pages
## Entity lists

# Extending
## Add new type
## Extend existing types
## Change existing definitions
### Fields
### Type resolver
### Entity lists


