---
title: Generic Data Index
---

# Pimcore Generic Data Index

The Pimcore Generic Data Index Bundle provides a centralized way to index and search elements (assets, data objects and documents) in Pimcore via indices (e.g OpenSearch, Elasticsearch).
It is shipped with the OpenSearch and Elasticsearch clients and provides a central configuration for them in order to be used in other bundles.
This bundle can be extended and customized to fit your specific needs, for example if you would like to extend the search indices with custom attributes.

## Features in a Nutshell
- Based on OpenSearch/Elasticsearch
- Centralized data index for multiple bundles (Portal Engine, Studio API/UI, etc.)
- Indexing of all documents, assets and data objects
- Provides search services and models to search, filter and aggregate the data saved in the search indices 

## Documentation Overview
- [Installation](./doc/01_Installation/README.md)
- [Configuration](./doc/02_Configuration/README.md)
- [Searching Data Index](./doc/04_Searching_For_Data_In_Index/README.md)
- [Extending Data Index](./doc/05_Extending_Data_Index/README.md)
