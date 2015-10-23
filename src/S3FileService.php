<?php
namespace Synapse\File;

use Aws\S3\S3Client;
use Synapse\Application;

class S3FileService extends AbstractFileService implements FileServiceInterface
{
    /**
     * @var S3Client
     */
    protected $s3;
    /**
     * @var string
     */
    protected $bucket;

    /**
     * @param array    $config
     * @param S3Client $s3
     * @codeCoverageIgnore
     */
    public function __construct(array $config, S3Client $s3)
    {
        $this->s3     = $s3;
        $this->bucket = $config['bucket'];
        $this->s3->registerStreamWrapper();
    }

    /**
     * Primarily used for testing
     * @param S3Client $s3
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function setS3Client(S3Client $s3)
    {
        $this->s3 = $s3;
        return $this;
    }

    /**
     * @param string          $path
     * @param string|resource $data
     * @param string          $contentType
     *
     * @return bool
     * @throws FileException
     */
    public function save($path, $data, $contentType = null)
    {
        try {
            $this->s3->putObject([
                'Bucket'      => $this->bucket,
                'Key'         => $path,
                'Body'        => $data,
                'ContentType' => $contentType,
            ]);
        } catch (\Exception $e) {
            throw new FileException('Unable to save file', $e->getCode(), $e);
        }

        return true;
    }

    /**
     * @param string $path
     *
     * @return resource
     */
    public function load($path)
    {
        return $this->openFile($path, 'rb');
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function loadFile(AbstractFileEntity $fileEntity)
    {
        return $this->load($fileEntity->getPath());
    }

    /**
     * @param $path
     *
     * @return bool
     * @throws FileException
     */
    public function delete($path)
    {
        try {
            $this->s3->deleteObject([
                'Bucket' => $this->bucket,
                'Key'    => $path,
            ]);
        } catch (\Exception $e) {
            throw new FileException('File was not removed from filesystem', $e->getCode(), $e);
        }

        return true;
    }

    /**
     * @param string          $path
     * @param string|resource $data
     *
     * @return mixed
     */
    public function update($path, $data)
    {
        $file = $this->openFile($path, 'wb');

        if (is_resource($data)) {
            stream_copy_to_stream($data, $file);
        } else {
            fwrite($file, $data);
        }

        return true;
    }

    /**
     *
     * @param string $oldName
     * @param string $newName
     *
     * @return bool - true
     */
    public function rename($oldName, $newName)
    {
        try {
            $this->s3->copyObject([
                'Bucket'     => $this->bucket,
                'Key'        => $newName,
                'CopySource' => sprintf('%s/%s', $this->bucket, $oldName),
            ]);

            $this->delete($oldName);
        } catch (\Exception $e) {
            throw new FileException('File was not renamed', $e->getCode(), $e);
        }

        return true;
    }

    /**
     * @param $path
     * @param $mode
     *
     * @return resource|bool
     */
    protected function openFile($path, $mode)
    {
        $location = "s3://{$this->bucket}/$path";

        return fopen($location, $mode);
    }

    protected $mdCache = [];

    protected function getMetaData($path)
    {
        if (!array_key_exists($path, $this->mdCache)) {
            try {
                $this->mdCache[$path] = $this->s3->headObject([
                    'Bucket' => $this->bucket,
                    'Key'    => $path,
                ])->toArray();
            } catch (\Exception $e) {
                throw new FileException('Unable to get metadata for file: ' . $path, $e->getCode(), $e);
            }
        }

        return $this->mdCache[$path];
    }

    /**
     * Get mime type for file
     *
     * @param string $path
     *
     * @return string
     */
    public function getContentType($path)
    {
        $md = $this->getMetaData($path);

        return $md['ContentType'];
    }

    /**
     * Get file size in bytes
     *
     * @param string $path
     *
     * @return int
     */
    public function getFileSize($path)
    {
        $md = $this->getMetaData($path);

        return $md['ContentLength'];
    }
}
