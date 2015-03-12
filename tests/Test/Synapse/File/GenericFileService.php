<?php
namespace Test\Synapse\File;

use Synapse\File\AbstractFileEntity;
use Synapse\File\AbstractFileService;

class GenericFileService extends AbstractFileService
{
    protected $saveThrowException = false;

    public function setSaveThrowsException($bool)
    {
        $this->saveThrowException = $bool;
    }

    /**
     * @param string          $path
     * @param string|resource $data
     *
     * @return bool
     * @throws \Exception
     */
    public function save($path, $data)
    {
        if ($this->saveThrowException) {
            throw new \Exception();
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
        return fopen('php://memory', 'r');
    }

    public function loadFile(AbstractFileEntity $fileEntity)
    {
        return $this->load($fileEntity->getPath().DIRECTORY_SEPARATOR.$fileEntity->getId());
    }

    /**
     * @param $path
     *
     * @return bool
     */
    public function delete($path)
    {
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
        return true;
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
        return 'text/plain';
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
        return 100;
    }
}
