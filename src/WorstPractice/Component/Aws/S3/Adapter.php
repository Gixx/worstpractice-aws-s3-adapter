<?php

/**
 * Worst Practice Aws S3 Adapter
 *
 * PHP version 8.0
 *
 * @copyright 2021 Worst Practice
 * @license   https://opensource.org/licenses/MIT The MIT License (MIT)
 *
 * @link http://www.worstpractice.dev
 */

declare(strict_types=1);

namespace WorstPractice\Component\Aws\S3;

use Aws\S3\S3Client;

/**
 * AWS S3 adapter.
 */
class Adapter implements AdapterInterface
{
    /** @var string */
    private string $bucket;
    /** @var string[] */
    private array $validSortByKeys = [
        self::OBJECT_SORT_BY_NAME,
        self::OBJECT_SORT_BY_NAME_DESC,
        self::OBJECT_SORT_BY_DATE,
        self::OBJECT_SORT_BY_DATE_DESC,
    ];

    /**
     * Adapter constructor.
     *
     * @param S3Client $s3Client  The AWS SDK S3 Client instance
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(private S3Client $s3Client)
    {
    }

    /**
     * Set active bucket.
     *
     * @param string $bucket
     */
    public function setBucket(string $bucket): void
    {
        $this->bucket = $bucket;
    }

    /**
     * Gets the last uploaded object's key in the given S3 bucket by key prefix.
     *
     * @param string $keyPrefix
     * @return string|null
     */
    public function getLastUploadedKeyByPrefix(string $keyPrefix): ?string
    {
        $object = $this->getObjectListByPrefix($keyPrefix, self::OBJECT_SORT_BY_DATE_DESC, 1);

        return $object[0]['Key'] ?? null;
    }

    /**
     * Gets the object list for the given S3 bucket by key prefix.
     *
     * @param string $keyPrefix The path on the S3 bucket. Mandatory.
     * @param string|null $sortBy Sort the results by the given key.
     *                            Use the constants because this parameter requires special values.
     * @param int $limit Limit the results. 0 means return all.
     * @return array
     */
    public function getObjectListByPrefix(string $keyPrefix, string $sortBy = null, int $limit = 0): array
    {
        $options = $this->getSearchOptions($keyPrefix, $sortBy, $limit);

        $results = $this->fetchFullFileList($options);
        // Avoid sort if not needed.
        $sortBy !== self::OBJECT_SORT_BY_NAME && $this->sortFileList($results, $sortBy);
        // Avoid limit if not needed.
        $limit && $this->limitFileList($results, $limit);

        return $results;
    }

    /**
     * Create the search options, and modify the sort and limit attributes if necessary.
     *
     * @param string $keyPrefix
     * @param string|null $sortBy
     * @param int $limit
     * @return array
     */
    private function getSearchOptions(string $keyPrefix, ?string &$sortBy, int &$limit): array
    {
        $options = [
            'Bucket' => $this->bucket,
            'EncodingType' => 'url',
            'Prefix' => $keyPrefix,
            'RequestPayer' => 'requester'
        ];

        if (empty($sortBy)) {
            $sortBy = self::OBJECT_SORT_BY_NAME;
        }

        $limit = (int) abs($limit);

        // We can add a query limit here only when we don't want any special sorting.
        if ($sortBy === self::OBJECT_SORT_BY_NAME && $limit < self::AWS_DEFAULT_LIST_LIMIT) {
            $options['MaxKeys'] = $limit;
            // Set the parameter to 0 to avoid the unnecessary array_chunk later.
            $limit = 0;
        }

        return $options;
    }

    /**
     * Fetches full file list.
     *
     * @param array $options
     * @return array
     */
    private function fetchFullFileList(array $options): array
    {
        $results = [];
        $continuationToken = '';

        do {
            $options['ContinuationToken'] = $continuationToken;

            $response = $this->s3Client->listObjectsV2($options);

            if (empty($response['Contents'])) {
                break;
            }

            $results[] = $response['Contents'];
            $continuationToken = $response['NextContinuationToken'];
            $isTruncated = $response['IsTruncated'];
            usleep(50000); // 50 ms pause to avoid CPU spikes
        } while ($isTruncated);

        return array_merge([], ...$results);
    }

    /**
     * Sorts file list by name or date, ascending or descending.
     *
     * @param array $fileList
     * @param string|null $sortBy
     * @return bool
     */
    private function sortFileList(array &$fileList, ?string $sortBy): bool
    {
        if (empty($fileList) || empty($sortBy) || !in_array($sortBy, $this->validSortByKeys, true)) {
            return false;
        }

        $direction = $sortBy[0] === '^' ? 'asc' : 'desc';
        $sortByKey = substr($sortBy, 1);

        return usort($fileList, static function ($a, $b) use ($direction, $sortByKey) {
            $cmp = strcmp($a[$sortByKey], $b[$sortByKey]);
            return $direction === 'asc' ? $cmp : -$cmp;
        });
    }

    /**
     * Limit the file list at most the given number of items.
     *
     * @param array $fileList
     * @param int $limit
     * @return bool
     */
    private function limitFileList(array &$fileList, int $limit): bool
    {
        if (empty($fileList) || $limit <= 0) {
            return false;
        }

        $fileList = array_chunk($fileList, $limit)[0];

        return true;
    }
}
