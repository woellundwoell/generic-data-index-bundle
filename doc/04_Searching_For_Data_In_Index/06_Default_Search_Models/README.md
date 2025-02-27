# Default Search Models

:::info

All models under namespace `Pimcore\Bundle\GenericDataIndexBundle\Model\OpenSearch` are deprecated and will be removed in version 2.0

Please use models from `Pimcore\Bundle\GenericDataIndexBundle\Model\DefaultSearch` instead.

:::

Default search models can be used when individual search queries are needed to streamline the creation of Elasticsearch or OpenSearch search JSONs.

This is especially useful when you want to create your own [search modifiers](../05_Search_Modifiers/README.md) or when you would like to create services which should directly execute searches through the search client. They are used by the Generic Data Index and its search services internally to handle the execution of search queries on a lower level.

## Example usage in search modifier

This example shows how to use a custom search modifier to add a term filter to the search query.

```php
#[AsSearchModifierHandler]
public function handleCustomFilter(CustomFilter $customFilter, SearchModifierContextInterface $context): void
{
    $context->getSearch()->addQuery(
        new TermFilter(
            field: $customFilter->getField(),
            term: $customFilter->getValue(),
        )
    );
}
```

## Search Model

The search model is the main model to create a search query. It can be used to add queries, filters, aggregations and sorting to the search.

```php
use Pimcore\Bundle\GenericDataIndexBundle\Model\DefaultSearch\Search;

$search = (new Search())
    ->setSize(10) // set the number of results to return
    ->setFrom(0) // set the offset of the results
    ->setSource(['field']) // set the fields to return
    ->addSort(new FieldSort('field', 'asc')) // add a sort
    ->addQuery(new TermQuery('field', 'value')) // add a query
    ->addAggregation(new Aggregation('test-aggregation',[...])) // add an aggregation
;

$result = $searchClient->search( [
      'index' => $indexName,
      'body' => $search->toArray()
]);
```

## Query Models

The query models are used to create a query for the search. They can be used to create any query which is supported by OpenSearch or Elasticsearch.

### BoolQuery

Represents a boolean query. It can be used to combine multiple queries with boolean operators. See documentation for [OpenSearch](https://opensearch.org/docs/latest/query-dsl/compound/bool/) or [Elasticsearch](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-bool-query.html) for more details.

#### Basic usage

```php
use Pimcore\Bundle\GenericDataIndexBundle\Model\DefaultSearch\Query\BoolQuery;

$boolQuery = new BoolQuery([
    'should' => [
        ['term' => ['field' => 'value']],
        ['term' => ['field2' => 'value2']],
    ],
]);
```

#### Add additional conditions
```php
$boolQuery = new BoolQuery();
$boolQuery->addCondition('must', [
    'term' => ['field' => 'value']
]);
```


#### Merge multiple queries
```php
$boolQueryA = new BoolQuery([
    'should' => [
        ['term' => ['field' => 'value']],
    ],
]);

$boolQueryB = new BoolQuery([
    'should' => [
        ['term' => ['field' => 'value']],
    ],
]);

// this will result in a query with two "should" conditions
$boolQueryA->merge($boolQueryB);
```

#### Use other queries in sub queries
```php
$boolQuery = new BoolQuery([
    'should' => [
        new TermFilter('field', 'value'),
        new TermFilter('field2', 'value2'),
    ]
]);
```

### TermFilter

The term filter combines a boolean query with a term query. It can be used to filter the search results by a term.

```php
use Pimcore\Bundle\GenericDataIndexBundle\Model\DefaultSearch\Query\TermFilter;
$termFilter = new TermFilter('field', 'value');
```

### TermsFilter

The terms filter combines a boolean query with a terms query. It can be used to filter the search results by multiple term.

### WildcardFilter

The wildcard filter combines a boolean query with a wildcard query. It can be used to filter the search results by terms using * as wildcard.

```php
use Pimcore\Bundle\GenericDataIndexBundle\Model\DefaultSearch\Query\WildcardFilter;
$wildcardFilter = new WildcardFilter('field', 'value*');
```

It is possible to influence the wildcard filter behaviour by setting additional options. Take a look at the constructor of the `WildcardFilter` class for more details.

### DateFilter

The date filter can be used to filter the search results by a date range or exact date.

```php
use Pimcore\Bundle\GenericDataIndexBundle\Model\DefaultSearch\Query\DateFilter;

// date range
$dateFilter = new DateFilter('datefield', strtotime('2000-01-01'), strtotime('2099-12-31'));

// exact date
$dateFilter = new DateFilter('datefield', null, null, strtotime('2000-01-01'));
```

The date filter rounds the timestamps to full days by default. If you want to use exact timestamps, you can set the `roundToDay` option to `false`.

```php
// exact timestamp
$dateFilter = new DateFilter('datefield', null, null, strtotime('2000-01-01 12:00:00'), false);
```



### Generic Query

The generic `Query` model can be used to create any query which is supported by OpenSearch or Elasticsearch. It can be used to create custom queries which are not covered by the other query models.

```php
use Pimcore\Bundle\GenericDataIndexBundle\Model\DefaultSearch\Query\Query;

$matchQuery = new Query('match', [
    'field' => 'value'
]);

$rangeQuery = new Query('range', [
    'field' => [
        'gte' => 10,
        'lte' => 20,
    ]
]);
```


## Aggregation Model

The aggregation model is used to create an aggregation for the search. It can be used to create any aggregation which is supported by OpenSearch or Elasticsearch. It's just a simple wrapper class without any special logic.

```php
use Pimcore\Bundle\GenericDataIndexBundle\Model\DefaultSearch\Aggregation\Aggregation;

$aggregation = new Aggregation('test-aggregation', [
    'terms' => [
        'field' => 'value',
    ],
]);
```
