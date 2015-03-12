<?php
namespace Synapse\File;

use Symfony\Component\HttpFoundation\Request;

interface FileServiceInterface
{
    /**
     * @param string          $path
     * @param string|resource $data
     *
     * @return bool
     */
    public function save($path, $data);

    /**
     * @param string $path
     *
     * @return resource
     */
    public function load($path);

    /**
     * @param AbstractFileEntity $fileEntity
     *
     * @return resource
     */
    public function loadFile(AbstractFileEntity $fileEntity);

    /**
     * @param $path
     *
     * @return bool
     */
    public function delete($path);

    /**
     * @param string          $path
     * @param string|resource $data
     *
     * @return mixed
     */
    public function update($path, $data);

    /**
     * Get mime type for file
     *
     * @param string $path
     *
     * @return string
     */
    public function getContentType($path);

    /**
     * Get file size in bytes
     *
     * @param string $path
     *
     * @return int
     */
    public function getFileSize($path);

    /**
     * @param Request            $request
     * @param AbstractFileMapper $fileMapper
     *
     * @return AbstractFileEntity
     */
    public function create(Request $request, AbstractFileMapper $fileMapper);
}
