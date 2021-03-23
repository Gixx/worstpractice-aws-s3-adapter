<?php

/**
 * Worst Practice Aws S3 Adapter Test
 *
 * PHP version 8.0
 *
 * @copyright 2021 Worst Practice
 * @license   https://opensource.org/licenses/MIT The MIT License (MIT)
 *
 * @link http://www.worstpractice.dev
 */

declare(strict_types=1);

namespace WorstPracticeTest\Component\Aws\S3;

use Aws\S3\S3Client;
use JsonException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use WorstPractice\Component\Aws\S3\Adapter as S3Adapter;

class AdapterTest extends PHPUnitTestCase
{
    public const S3_BUCKET = [
        'total-bad' => [
            '' => null
        ],
        'no-result' => [
            '' => [
                'Contents' => null,
                'NextContinuationToken' => '',
                'IsTruncated' => false
            ]
        ],
        'one-result' => [
            '' => [
                'Contents' => [
                    ['Key' => 'one-result/data1.txt', 'LastModified' => '2020-03-03 10:00:00']
                ],
                'NextContinuationToken' => '',
                'IsTruncated' => false
            ]
        ],
        'three-results' => [
            '' => [
                'Contents' => [
                    ['Key' => 'one-result/data3.txt', 'LastModified' => '2020-03-04 10:00:00'],
                    ['Key' => 'one-result/data1.txt', 'LastModified' => '2020-03-03 10:00:00'],
                    ['Key' => 'one-result/data2.txt', 'LastModified' => '2020-03-05 10:00:00'],
                ],
                'NextContinuationToken' => '',
                'IsTruncated' => false
            ]
        ],
        'paged-results' => [
            '' => [
                'Contents' => [
                    ['Key' => 'one-result/data3.txt', 'LastModified' => '2020-03-04 10:00:00'],
                    ['Key' => 'one-result/data1.txt', 'LastModified' => '2020-03-03 10:00:00'],
                    ['Key' => 'one-result/data2.txt', 'LastModified' => '2020-03-05 10:00:00'],
                ],
                'NextContinuationToken' => 'abcde1234567890',
                'IsTruncated' => true
            ],
            'abcde1234567890' => [
                'Contents' => [
                    ['Key' => 'one-result/data5.txt', 'LastModified' => '2020-03-04 11:00:00'],
                    ['Key' => 'one-result/data6.txt', 'LastModified' => '2020-03-05 18:00:00'],
                    ['Key' => 'one-result/data7.txt', 'LastModified' => '2020-03-06 10:00:00'],
                ],
                'NextContinuationToken' => '1234567890abcde',
                'IsTruncated' => true
            ],
            '1234567890abcde' => [
                'Contents' => [
                    ['Key' => 'one-result/data4.txt', 'LastModified' => '2020-03-07 10:00:00'],
                    ['Key' => 'one-result/data9.txt', 'LastModified' => '2020-03-03 09:00:00'],
                    ['Key' => 'one-result/data8.txt', 'LastModified' => '2020-03-08 10:00:00'],
                ],
                'NextContinuationToken' => '',
                'IsTruncated' => false
            ],
        ],
    ];

    /**
     * Data provider for the testListSorter method.
     *
     * @return array[]
     */
    public function s3ApiResponseDataProvider(): array
    {
        return [
            'testInvalid' => [
                'prefix' => 'total-bad',
                'sortBy' => null,
                'limit' => 0,
                'expectedResult' => [],
            ],
            'testNoResult' => [
                'prefix' => 'empty',
                'sortBy' => null,
                'limit' => 0,
                'expectedResult' => [],
            ],
            'testOneResult' => [
                'prefix' => 'one-result',
                'sortBy' => null,
                'limit' => 0,
                'expectedResult' => [
                    ['Key' => 'one-result/data1.txt', 'LastModified' => '2020-03-03 10:00:00']
                ],
            ],
            'testOneResultWitSort' => [
                'prefix' => 'one-result',
                'sortBy' => S3Adapter::OBJECT_SORT_BY_DATE_DESC,
                'limit' => 0,
                'expectedResult' => [
                    ['Key' => 'one-result/data1.txt', 'LastModified' => '2020-03-03 10:00:00']
                ],
            ],
            'testOneResultWithSortAndLimit' => [
                'prefix' => 'one-result',
                'sortBy' => S3Adapter::OBJECT_SORT_BY_NAME,
                'limit' => 3,
                'expectedResult' => [
                    ['Key' => 'one-result/data1.txt', 'LastModified' => '2020-03-03 10:00:00']
                ],
            ],
            'testMoreResultWithSortByName' => [
                'prefix' => 'three-results',
                'sortBy' => S3Adapter::OBJECT_SORT_BY_NAME,
                'limit' => 0,
                'expectedResult' => [
                    ['Key' => 'one-result/data1.txt', 'LastModified' => '2020-03-03 10:00:00'],
                    ['Key' => 'one-result/data2.txt', 'LastModified' => '2020-03-05 10:00:00'],
                    ['Key' => 'one-result/data3.txt', 'LastModified' => '2020-03-04 10:00:00'],
                ],
            ],
            'testMoreResultWithSortByNameDesc' => [
                'prefix' => 'three-results',
                'sortBy' => S3Adapter::OBJECT_SORT_BY_NAME_DESC,
                'limit' => 0,
                'expectedResult' => [
                    ['Key' => 'one-result/data3.txt', 'LastModified' => '2020-03-04 10:00:00'],
                    ['Key' => 'one-result/data2.txt', 'LastModified' => '2020-03-05 10:00:00'],
                    ['Key' => 'one-result/data1.txt', 'LastModified' => '2020-03-03 10:00:00'],
                ],
            ],
            'testMoreResultWithSortByDate' => [
                'prefix' => 'three-results',
                'sortBy' => S3Adapter::OBJECT_SORT_BY_DATE,
                'limit' => 0,
                'expectedResult' => [
                    ['Key' => 'one-result/data1.txt', 'LastModified' => '2020-03-03 10:00:00'],
                    ['Key' => 'one-result/data3.txt', 'LastModified' => '2020-03-04 10:00:00'],
                    ['Key' => 'one-result/data2.txt', 'LastModified' => '2020-03-05 10:00:00'],
                ],
            ],
            'testMoreResultWithSortByDateDesc' => [
                'prefix' => 'three-results',
                'sortBy' => S3Adapter::OBJECT_SORT_BY_DATE_DESC,
                'limit' => 0,
                'expectedResult' => [
                    ['Key' => 'one-result/data2.txt', 'LastModified' => '2020-03-05 10:00:00'],
                    ['Key' => 'one-result/data3.txt', 'LastModified' => '2020-03-04 10:00:00'],
                    ['Key' => 'one-result/data1.txt', 'LastModified' => '2020-03-03 10:00:00'],
                ],
            ],
            'testAllResultWithSortByDate' => [
                'prefix' => 'paged-results',
                'sortBy' => S3Adapter::OBJECT_SORT_BY_DATE,
                'limit' => 0,
                'expectedResult' => [
                    ['Key' => 'one-result/data9.txt', 'LastModified' => '2020-03-03 09:00:00'],
                    ['Key' => 'one-result/data1.txt', 'LastModified' => '2020-03-03 10:00:00'],
                    ['Key' => 'one-result/data3.txt', 'LastModified' => '2020-03-04 10:00:00'],
                    ['Key' => 'one-result/data5.txt', 'LastModified' => '2020-03-04 11:00:00'],
                    ['Key' => 'one-result/data2.txt', 'LastModified' => '2020-03-05 10:00:00'],
                    ['Key' => 'one-result/data6.txt', 'LastModified' => '2020-03-05 18:00:00'],
                    ['Key' => 'one-result/data7.txt', 'LastModified' => '2020-03-06 10:00:00'],
                    ['Key' => 'one-result/data4.txt', 'LastModified' => '2020-03-07 10:00:00'],
                    ['Key' => 'one-result/data8.txt', 'LastModified' => '2020-03-08 10:00:00'],
                ],
            ],
            'testAllResultWithSortByNameDescAndLimit' => [
                'prefix' => 'paged-results',
                'sortBy' => S3Adapter::OBJECT_SORT_BY_NAME_DESC,
                'limit' => 5,
                'expectedResult' => [
                    ['Key' => 'one-result/data9.txt', 'LastModified' => '2020-03-03 09:00:00'],
                    ['Key' => 'one-result/data8.txt', 'LastModified' => '2020-03-08 10:00:00'],
                    ['Key' => 'one-result/data7.txt', 'LastModified' => '2020-03-06 10:00:00'],
                    ['Key' => 'one-result/data6.txt', 'LastModified' => '2020-03-05 18:00:00'],
                    ['Key' => 'one-result/data5.txt', 'LastModified' => '2020-03-04 11:00:00'],
                ],
            ],
            'testAllResultWithLimitOnly' => [
                'prefix' => 'paged-results',
                'sortBy' => null,
                'limit' => 2,
                'expectedResult' => [
                    ['Key' => 'one-result/data3.txt', 'LastModified' => '2020-03-04 10:00:00'],
                    ['Key' => 'one-result/data1.txt', 'LastModified' => '2020-03-03 10:00:00'],
                ],
            ]
        ];
    }

    /**
     * @dataProvider s3ApiResponseDataProvider
     *
     * @param string $prefix
     * @param string|null $sortBy
     * @param int $limit
     * @param array $expectedResult
     * @throws JsonException
     */
    public function testListSorter(string $prefix, ?string $sortBy, int $limit, array $expectedResult): void
    {
        $s3Adapter = new S3Adapter($this->getS3Client());
        $s3Adapter->setBucket('test-bucket');

        $actualResult = $s3Adapter->getObjectListByPrefix($prefix, $sortBy, $limit);

        self::assertSame(
            json_encode($expectedResult, JSON_THROW_ON_ERROR),
            json_encode($actualResult, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * Tests the getLastUploadedKeyByPrefix() method
     */
    public function testGetLastUploadedKeyByPrefix(): void
    {
        $s3Adapter = new S3Adapter($this->getS3Client());
        $s3Adapter->setBucket('test-bucket');

        $expectedResult = 'one-result/data8.txt';
        $actualResult = $s3Adapter->getLastUploadedKeyByPrefix('paged-results');
        self::assertSame($expectedResult, $actualResult);
    }

    /**
     * Tests the getLastUploadedKeyByPrefix() method for a non-existing path
     */
    public function testGetLastUploadedKeyByPrefixForEmptyFolder(): void
    {
        $s3Adapter = new S3Adapter($this->getS3Client());
        $s3Adapter->setBucket('test-bucket');

        $actualResult = $s3Adapter->getLastUploadedKeyByPrefix('some/path');
        self::assertNull($actualResult);
    }

    /**
     * Creates a mocked S3 Client instance.
     *
     * @return S3Client|MockObject
     */
    protected function getS3Client(): S3Client|MockObject
    {
        $s3Bucket = self::S3_BUCKET;
        $s3Client = $this->getMockBuilder(S3Client::class)
            ->disableOriginalConstructor()
            ->addMethods(['listObjectsV2'])
            ->getMock();

        $s3Client->expects(self::atLeast(1))
            ->method('listObjectsV2')
            ->willReturnCallback(static function ($options) use ($s3Bucket) {
                $prefix = $options['Prefix'];
                $next = $options['ContinuationToken'] ?? '';

                $result = $s3Bucket[$prefix][$next] ?? $s3Bucket['no-result'][''];

                $awsLimiter = $options['MaxKeys'] ?? 0;

                if (!empty($result)
                    && !empty($awsLimiter)
                    && $awsLimiter < count($result['Contents'])
                ) {
                    $chunkedResults = array_chunk($result['Contents'], $awsLimiter)[0];
                    $result['Contents'] = $chunkedResults;
                    $result['IsTruncated'] = false;
                }

                return $result;
            });

        return $s3Client;
    }
}
