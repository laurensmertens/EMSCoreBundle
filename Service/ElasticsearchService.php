<?php

namespace EMS\CoreBundle\Service;

use Elastica\Client as ElasticaClient;
use Elastica\Query\BoolQuery;
use Elastica\Query\Term;
use Elastica\Search;
use Elasticsearch\Client;
use EMS\CommonBundle\Common\Document;
use EMS\CommonBundle\Elasticsearch\Request\RequestInterface;
use EMS\CommonBundle\Elasticsearch\Response\Response;
use EMS\CommonBundle\Elasticsearch\Response\ResponseInterface;
use EMS\CommonBundle\Helper\EmsFields;
use EMS\CoreBundle\Entity\ContentType;
use EMS\CoreBundle\Entity\Environment;
use EMS\CoreBundle\Exception\SingleResultException;
use Psr\Log\LoggerInterface;

class ElasticsearchService
{

    /** @var LoggerInterface */
    private $logger;

    /** @var Client */
    private $client;

    /** @var ElasticaClient */
    private $elasticaClient;

    public function __construct(Client $client, LoggerInterface $logger, ElasticaClient $elasticaClient)
    {
        $this->client = $client;
        $this->logger = $logger;
        $this->elasticaClient = $elasticaClient;
    }

    public function getVersion(): string
    {
        return $this->elasticaClient->getVersion();
    }

    public function get(Environment $environment, ContentType $contentType, string $ouuid): Document
    {
        $termId = new Term();
        $termId->setTerm('_id', $ouuid);

        $termType = new Term();
        $termType->setTerm('_type', $contentType->getName());

        $termContentType = new Term();
        $termContentType->setTerm('_contenttype', $contentType->getName());

        $query = new BoolQuery();
        $query->addMust($termId);
        $query->setMinimumShouldMatch(1);
        $query->addShould($termType);
        $query->addShould($termContentType);

        $search = new Search($this->elasticaClient);
        $resultSet = $search->addIndex($environment->getAlias())->search($query, 1);

        if ($resultSet->getTotalHits() === 0) {
            $this->logger->error('log.elasticsearch.too_few_document_result', [
                'total' => $resultSet->getTotalHits(),
                EmsFields::LOG_CONTENTTYPE_FIELD => $contentType->getName(),
                EmsFields::LOG_OUUID_FIELD => $ouuid,
                EmsFields::LOG_ENVIRONMENT_FIELD => $environment->getName(),
            ]);
            throw new SingleResultException('Expected one result, got 0');
        }

        if ($resultSet->getTotalHits() !== 1) {
            $this->logger->error('log.elasticsearch.too_many_document_result', [
                'total' => $resultSet->getTotalHits(),
                EmsFields::LOG_CONTENTTYPE_FIELD => $contentType->getName(),
                EmsFields::LOG_OUUID_FIELD => $ouuid,
                EmsFields::LOG_ENVIRONMENT_FIELD => $environment->getName(),
            ]);
            throw new SingleResultException(sprintf('Expected one result, got %s', $resultSet->getTotalHits()));
        }

        /** @var \Elastica\Document $document */
        foreach ($resultSet->getDocuments() as $document) {
            $data = $document->getData();
            if (is_string($data)) {
                throw new \RuntimeException('Unexpected string as data');
            }
            return new Document($contentType->getName(), $ouuid, $data);
        }
        throw new \RuntimeException('Unexpected empty result set');
    }


    /**
     * @return int|bool
     */
    public function compare(string $version)
    {
        return \version_compare($this->getVersion(), $version);
    }

    /**
     * @return array<string, string>
     */
    public function getKeywordMapping(): array
    {
        if (version_compare($this->getVersion(), '5') > 0) {
            return [
                'type' => 'keyword',
            ];
        }
        return [
            'type' => 'string',
            'index' => 'not_analyzed'
        ];
    }


    /**
     * @param array<string, string>  $in
     * @return array<string, string>
     */
    public function convertMapping(array $in): array
    {
        $out = $in;
        if (version_compare($this->getVersion(), '5') > 0) {
            if (isset($out['analyzer']) && $out['analyzer'] === 'keyword') {
                $out['type'] = 'keyword';
                unset($out['analyzer']);
                unset($out['fielddata']);
                unset($out['index']);
            } elseif (isset($out['index']) && $out['index'] === 'not_analyzed') {
                $out['type'] = 'keyword';
                unset($out['analyzer']);
                unset($out['fielddata']);
                unset($out['index']);
            } elseif (isset($out['type']) && $out['type'] === 'string') {
                $out['type'] = 'text';
            } elseif (isset($out['type']) && $out['type'] === 'keyword') {
                unset($out['analyzer']);
                unset($out['fielddata']);
                unset($out['index']);
            }
        }
        return $out;
    }


    /**
     * @param array<string, string> $mapping
     * @return array<string, bool|string|string[]>
     */
    public function updateMapping(array $mapping): array
    {

        if (isset($mapping['copy_to']) && !empty($mapping['copy_to']) && is_string($mapping['copy_to'])) {
            $mapping['copy_to'] = explode(',', $mapping['copy_to']);
        }

        if (version_compare($this->getVersion(), '5') > 0) {
            if ($mapping['type'] === 'string') {
                if ((isset($mapping['analyzer']) && $mapping['analyzer'] === 'keyword') || (empty($mapping['analyzer']) && isset($mapping['index']) && $mapping['index'] === 'not_analyzed')) {
                    $mapping['type'] = 'keyword';
                    unset($mapping['analyzer']);
                } else {
                    $mapping['type'] = 'text';
                }
            }

            if (isset($mapping['index']) && $mapping['index'] === 'No') {
                $mapping['index'] = false;
            }
            if (isset($mapping['index']) && $mapping['index'] !== false) {
                $mapping['index'] = true;
            }
        }
        return $mapping;
    }
    
    /**
     * @return array<string, string>
     */
    public function getDateTimeMapping(): array
    {
        return [
            'type' => 'date',
            'format' => 'date_time_no_millis'
        ];
    }

    /**
     * @return array<string, string|false>
     */
    public function getNotIndexedStringMapping(): array
    {
        if (version_compare($this->getVersion(), '5') > 0) {
            return [
                'type' => 'text',
                'index' => false,
            ];
        }
        return [
            'type' => 'string',
            'index' => 'no'
        ];
    }

    /**
     * @return array<string, string|true>
     */
    public function getIndexedStringMapping()
    {
        if (version_compare($this->getVersion(), '5') > 0) {
            return [
                'type' => 'text',
                'index' => true,
            ];
        }
        return [
            'type' => 'string',
            'index' => 'analyzed'
        ];
    }

    /**
     * @return array<string, string>
     */
    public function getLongMapping(): array
    {
        return [
            "type" => "long",
        ];
    }

    public function withAllMapping(): bool
    {
        return version_compare($this->getVersion(), '5.6') < 0;
    }

    /**
     * @return iterable|ResponseInterface[]
     */
    public function scroll(RequestInterface $request): iterable
    {
        $scrollResponse = new Response($this->client->search($request->toArray()));

        while ($scrollResponse->hasDocuments()) {
            yield $scrollResponse;

            $scrollResponse = new Response($this->client->scroll([
                'scroll_id' =>  $scrollResponse->getScrollId(),
                'scroll' => $request->getScroll()
            ]));
        }
    }
}
