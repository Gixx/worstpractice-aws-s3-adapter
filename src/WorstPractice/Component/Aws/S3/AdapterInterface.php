<?php

/**
 * Worst Practice Aws S3 Adapter Interface
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

/**
 * AWS S3 adapter.
 */
interface AdapterInterface
{
    public const AWS_DEFAULT_LIST_LIMIT = 1000;

    public const OBJECT_SORT_BY_NAME = '^Key';
    public const OBJECT_SORT_BY_NAME_DESC = 'vKey';
    public const OBJECT_SORT_BY_DATE = '^LastModified';
    public const OBJECT_SORT_BY_DATE_DESC = 'vLastModified';

    /**
     * Set active bucket.
     *
     * @param string $bucket
     */
    public function setBucket(string $bucket): void;

    /**
     * Gets the last uploaded object's key in the given S3 bucket by key prefix.
     *
     * @param string $keyPrefix
     * @return string|null
     */
    public function getLastUploadedKeyByPrefix(string $keyPrefix): ?string;

    /**
     * Gets the object list for the given S3 bucket by key prefix.
     *
     * @param string $keyPrefix The path on the S3 bucket. Mandatory.
     * @param string|null $sortBy Sort the results by the given key.
     *                            Use the constants because this parameter requires special values.
     * @param int $limit Limit the results. 0 means return all.
     * @return array
     */
    public function getObjectListByPrefix(string $keyPrefix, string $sortBy = null, int $limit = 0): array;
}
