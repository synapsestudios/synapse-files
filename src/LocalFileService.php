<?php
namespace Synapse\File;

/**
 * Class LocalFileService
 *
 * @package Synapse\File
 *
 * @group   filesystem
 */
class LocalFileService extends AbstractFileService implements FileServiceInterface
{
    protected $basePath;

    /**
     *
     * @param string $basePath
     *
     * @throws FileException
     * @codeCoverageIgnore
     */
    public function __construct($basePath)
    {
        $this->basePath = rtrim($basePath, '/');
        if (!file_exists($basePath)) {
            if (!mkdir($basePath, 0777, true)) {
                throw new \RuntimeException('Unable to create base path for file storage (' . $basePath . ')');
            }
        } elseif (!is_dir($basePath)) {
            throw new \RuntimeException('Base path for file service is not a directory (' . $basePath . ')');
        }
        if (!is_writable($this->basePath)) {
            throw new FileException('Cannot write to base path');
        }
    }


    /**
     *
     * @param string          $path
     * @param string|resource $data
     *
     * @return bool - true if file saved
     * @throws FileException
     */
    public function save($path, $data)
    {
        $path     = trim($path, '/');
        $fullPath = $this->basePath . DIRECTORY_SEPARATOR;
        if (!empty($path)) {
            $fullPath .= $path;
        }

        if (file_exists($fullPath)) {
            throw new FileExistsException($fullPath);
        }

        $pathInfo = pathinfo($fullPath);
        $dir      = $pathInfo['dirname'];
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        if (is_file($dir)) {
            throw new FileException('Path given is the name of a file');
        }
        $this->checkIsDir($dir);

        $file = $this->openLocalFile($fullPath);

        if (is_resource($data)) {
            stream_copy_to_stream($data, $file);
        } else {
            fputs($file, $data);
        }

        fclose($file);
        return true;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function checkLocalFileExists($fullPath, $path)
    {
        if (!file_exists($fullPath)) {
            throw new FileException('Could not find file: ' . $path);
        }
    }

    /**
     * @param $fullPath
     *
     * @return resource
     * @throws FileException
     * @codeCoverageIgnore
     */
    protected function openLocalFile($fullPath)
    {
        $file = fopen($fullPath, 'w');
        if (!$file) {
            throw new FileException('Could not open file for writing');
        }
        return $file;
    }

    /**
     * @param $dir
     *
     * @throws FileException
     * @codeCoverageIgnore
     */
    protected function checkIsDir($dir)
    {
        if (!is_dir($dir)) {
            throw new FileException('Unable to create directory');
        }
    }
    /**
     * {@inheritdoc}
     */
    public function load($path)
    {
        $fullPath = $this->basePath . DIRECTORY_SEPARATOR . $path;
        $this->checkLocalFileExists($fullPath, $path);
        $file = fopen($fullPath, 'r');

        return $file;
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
     *
     * @param $path
     *
     * @return bool
     * @throws FileException
     * @codeCoverageIgnore
     */
    public function delete($path)
    {
        $fullPath = $this->basePath . DIRECTORY_SEPARATOR . $path;
        $this->checkLocalFileExists($fullPath, $path);
        $success = unlink($fullPath);
        if (!$success) {
            return false;
        }

        return true;
    }

    /**
     *
     * @param string          $path
     * @param string|resource $data
     *
     * @return mixed
     * @throws FileException
     */
    public function update($path, $data)
    {
        $fullPath = $this->basePath . DIRECTORY_SEPARATOR . $path;
        $this->checkLocalFileExists($fullPath, $path);
        $file = fopen($fullPath, 'w');
        if (is_resource($data)) {
            stream_copy_to_stream($data, $file);
        } else {
            fputs($file, $data);
        }

        return true;
    }

    /**
     *
     * @param string $path
     *
     * @return string
     */
    public function getContentType($path)
    {
        $fullPath = $this->basePath . DIRECTORY_SEPARATOR . $path;

        return mime_content_type($fullPath);
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
        $fullPath = $this->basePath . DIRECTORY_SEPARATOR . $path;

        return filesize($fullPath);
    }
}
